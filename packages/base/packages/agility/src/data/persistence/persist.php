<?php

namespace Agility\Data\Persistence;

use Agility\Data\Relation;
use Aqua\Attribute;
use ArrayUtils\Arrays;
use Exception;
use StringHelpers\Str;

	trait Persist {

		static function create() {

			$obj = forward_static_call_array([static::class, "new"], func_get_args());
			$obj->save();

			return $obj;

		}

		private function _createNew() {

			$this->_runCallbacks("beforeCreate");

			$attributes = $this->fetchAttributes(false);

			$relation = new Relation(static::class, Relation::Insert);
			if (($id = $relation->insert($attributes)->execute) == 0) {
				return false;
			}
			$this->_setAttribute(static::$primaryKey, $id);

			$this->_fresh = false;
			$this->_dirty = false;
			$this->_persisted = true;

			$this->_runCallbacks("afterCreate");
			return true;

		}

		private function createOrUpdate() {

			$this->_performValidations($this->_fresh);
			if ($this->invalid) {
				return false;
			}
			if (!$this->_dirty && !$this->_fresh) {
				return false;
			}

			$this->_runCallbacks("beforeSave");

			if ($this->_fresh) {
				$result = $this->_createNew();
			}
			else {
				$result = $this->_update();
			}
			$this->_backups = new Arrays;

			$this->_runCallbacks("afterSave");
			return $result;

		}

		function delete() {

			if ($this->_fresh) {
				throw new Exception("Cannot delete a fresh object of class ".static::class);
			}

			$this->_runCallbacks("beforeDelete");

			$relation = new Relation(static::class, Relation::Delete);
			$primaryKey = Str::pascalCase(static::$primaryKey);
			if ($relation->delete([static::$primaryKey => $this->_getAttribute($primaryKey)])->execute == 0) {
				return false;
			}

			$this->_deleted = true;

			$this->_runCallbacks("afterDelete");
			return true;

		}

		static function insert($params = []) {

			if (isset($params[0])) {

				foreach ($params as $param) {
					static::insert($param);
				}

				return;

			}

			$relation = new Relation(static::class, Relation::Insert);
			foreach ($params as $name => $value) {

				$name = Str::snakeCase($name);

				if (!is_a($name, Attribute::class)) {
					$name = static::aquaTable()->$name;
				}

				$relation->statement->insert([$name, $value]);

			}

			return $relation->execute();

		}

		static function new() {

			$obj = new static;

			$args = func_get_args();
			if (count($args) > 0) {

				if (is_array($args[0]) || is_a($args[0], Arrays::class)) {
					$obj->fillAttributes($args[0]);
				}
				else if (is_callable($args[0])) {
					($args[0]->bindTo($obj))();
				}

			}

			return $obj;

		}

		function refresh() {

			$this->attributes = static::find($this->valueOfPrimaryKey())->attributes;

			$this->_fresh = false;
			$this->_dirty = false;
			$this->_persisted = false;

		}

		function save() {
			return $this->createOrUpdate();
		}

		private function _update() {

			$this->_runCallbacks("beforeUpdate", $this->_backups);

			$attributes = $this->fetchAttributes(false, true);
			$primaryKey = $attributes[static::$primaryKey];
			unset($attributes[static::$primaryKey]);

			$relation = new Relation(static::class, Relation::Update);
			if ($relation->update($attributes)->where([static::$primaryKey => $primaryKey])->execute() == 0) {
				return false;
			}

			$this->_dirty = false;
			$this->_persisted = true;

			$this->_runCallbacks("afterUpdate", $this->_backups);
			return true;

		}

		function update($collection = []) {

			if (!empty($collection)) {

				$this->fillAttributes($collection);
				$this->_dirty = true;
				return $this->save();

			}

			return false;

		}

		static function updateAll($params = [], $where = []) {

			$relation = new Relation(static::class, Relation::Update);
			foreach ($params as $name => $value) {

				if (!is_a($name, Attribute::class)) {
					$name = static::aquaTable()->$name;
				}

				$relation->set([$name, $value]);

			}

			if (!empty($where)) {
				$relation->where($where);
			}

			return $relation->execute();

		}

	}

?>