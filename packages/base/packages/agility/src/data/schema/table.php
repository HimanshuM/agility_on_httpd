<?php

namespace Agility\Data\Schema;

use Agility\Data\Types\AutoIncrementOnNonIntTypeException;
use Agility\Data\Types\Base;
use Agility\Data\Types\UInt;
use Agility\Data\Types\DatetimeDb;
use Agility\Exceptions\InvalidArgumentTypeException;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	class Table {

		use Accessor;

		protected $name;
		protected $primaryKey;
		protected $attributes;

		protected $indexed = [];
		protected $unique = [];
		protected $foreignKey = [];

		protected $options;

		protected $connection;

		function __construct($name, $primaryKey = true, $options = []) {

			$this->name = $name;
			$this->attributes = new Arrays;
			$this->primaryKey = new Arrays;
			$this->options = $options;

			if (!empty($primaryKey)) {

				if ($primaryKey === true) {
					$primaryKey = "id";
				}

				$this->attributes[$primaryKey] = new Attribute($primaryKey, new UInt, false, null, true, false, false, null, "");
				$this->primaryKey($primaryKey, true);

			}

			$this->readonly("name", "indexed", "unique", "foreignKey");

		}

		function __call($name, $args = []) {

			$dataType = Base::getType($name);

			if (count($args) == 0) {
				throw new Exception("Attribute name not specified in call to ".$name."()", 1);
			}
			if (!is_string($args[0])) {
				throw new InvalidArgumentTypeException("Agility\\Data\\Migration\\Table::".$dataType, 0, "string", gettype($args[0]));
			}
			$attrName = $args[0];

			$options = [];
			if (isset($args[1])) {

				if (!is_array($args[1])) {
					throw new InvalidArgumentTypeException("Agility\\Data\\Migration\\Table::".$dataType, 1, "array", gettype($args[0]));
				}
				$options = $args[1];

			}

			return $this->column($attrName, $dataType, $options);

		}

		function column($attrName, $dataType, $options = []) {

			if (!is_a($dataType, Base::class)) {
				$dataType = Base::getType($dataType);
			}

			if (!empty($options["autoIncrement"]) && !is_a($dataType, "Agility\\Data\\Types\\Integer")) {
				throw new AutoIncrementOnNonIntTypeException($dataType, $attrName);
			}

			// Send options to the data type for consumption of limit, precision, size, foreignKey or polymorphic, etc
			$dataType->setParameters($options);

			$attribute = Attribute::buildFromOptions($attrName, $dataType, $options);
			if ($attribute->autoIncrement) {
				$this->primaryKey($attrName, true);
			}

			return $this->attributes[$attrName] = $attribute;

		}

		protected function compileAttribute($attribute) {

			$names;
			$query;
			if ($attribute->dataType."" == "reference") {

				$dataType = $attribute->dataType->nativeType($this->connection->getTypeMapper(), $attribute->name);
				if (!is_array($dataType)) {
					// Foreign key relationship
					$query = $this->compileNonPolymorphicAttribute($attribute, $dataType);
					$names = $attribute->name;

					$this->foreignKey[] = ForeignKeyRelation::build($this->name, $attribute->dataType->foreignKey, [
						"column" => $attribute->name,
						"onUpdate" => $attribute->dataType->onUpdate,
						"onDelete" => $attribute->dataType->onDelete,
					]);

				}
				else {

					$query = [];
					$names = [];

					foreach ($dataType as $name => $type) {

						$column = "`".$name."` ".$type;
						if (!$attribute->nullable) {
							$column .= " NOT NULL";
						}

						if (!is_null($attribute->onUpdate)) {
							$query .= " ON UPDATE ".$attribute->onUpdate;
						}

						if (!empty($attribute->comment)) {
							$query .= " COMMENT ".$attribute->comment;
						}

						$names[] = $name;
						$query[] = $column;

					}

					$query = implode(",\r\n", $query);

				}

			}
			else {

				$dataType = $attribute->dataType->nativeType($this->connection->getTypeMapper());
				$query = $this->compileNonPolymorphicAttribute($attribute, $dataType);

				$names = $attribute->name;

			}

			if ($attribute->indexed) {
				$this->indexed[] = $names;
			}
			else if ($attribute->unique) {
				$this->unique[] = $names;
			}

			return $query;

		}

		protected function compileAttributes() {

			if ($this->attributes->empty) {
				return "";
			}

			$query = [];
			foreach ($this->attributes as $attribute) {
				$query[] = $this->compileAttribute($attribute);
			}

			return implode(",\r\n", $query);

		}

		protected function compileNonPolymorphicAttribute($attribute, $dataType) {
			return $attribute->toSql($this->connection);
		}

		protected function compilePrimaryKey() {

			if (!empty($this->primaryKey)) {
				return "PRIMARY KEY (".$this->primaryKey->map(function($e) { return "`".$e->name."`"; })->implode(", ").")";
			}

			return "";

		}

		function primaryKey($attrName, $autoIncrement = false) {

			if ($autoIncrement) {

				if (!$this->primaryKey->empty) {
					throw new MultipleAutoIncrementException($this->name);
				}

			}

			$this->primaryKey[] = $this->attributes[$attrName];

		}

		function timestamps() {

			$this->attributes["created_at"] = Attribute::buildFromOptions("created_at", new DatetimeDb, ["default" => DatetimeDb::CurrentTimestamp])->index();
			$this->attributes["updated_at"] = Attribute::buildFromOptions("updated_at", new DatetimeDb, ["default" => DatetimeDb::CurrentTimestamp, "onUpdate" => DatetimeDb::CurrentTimestamp])->index();

		}

		function toSql($connection) {

			$this->connection = $connection;

			$query = "CREATE TABLE IF NOT EXISTS `".$this->name."` (\r\n";
			$attributesSql = $this->compileAttributes();
			if (!empty($attributesSql)) {

				$query .= $attributesSql.",\r\n";
				$query .= $this->compilePrimaryKey()."\r\n";

			}

			$query .= ") ENGINE=";
			if (!empty($this->options["engine"])) {
				$query .= $this->options["engine"];
			}
			else {
				$query .= $this->connection->defaultEngine();
			}
			if (isset($this->options["charSet"])) {
				$query .= " DEFAULT CHARSET=".$this->options["charSet"];
			}
			else {
				$query .= " DEFAULT CHARSET=".$this->connection->charSet;
			}
			if (!empty($this->options["collation"])) {
				$query .= " COLLATE ".$this->options["collation"];
			}
			if (!empty($this->options["comment"])) {
				$query .= " COMMENT ".$this->options["comment"];
			}

			return $query.";";

		}

	}

?>