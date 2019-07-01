<?php

namespace Agility\Data;

use AttributeHelper\Accessor;
use Aqua\Attribute;
use Aqua\Binary;
use Aqua\DeleteStatement;
use Aqua\InsertStatement;
use Aqua\Order;
use Aqua\SelectStatement;
use Aqua\Table;
use Aqua\UpdateStatement;
use Aqua\Visitors\Sanitizer;
use ArrayUtils\Arrays;
use ArrayUtils\Helpers\IndexFetcher;
use DateTime;
use Iterator;
use JsonSerializable;
use Phpm\Exceptions\ClassNotFoundException;
use Serializable;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class Relation implements Iterator, Serializable, JsonSerializable {

		use Accessor;
		use IndexFetcher;
		use Sanitizer;
		use Relations\CollectionCache;

		const Select = 1;
		const Insert = 2;
		const Update = 3;
		const Delete = 4;

		protected $_model;
		protected $_aquaTable;
		protected $_connection;
		protected $_statement;

		protected $_transformToModel = true;
		protected $_includes = false;

		// Used for pluck()
		protected $_fetchMode = 0;

		const Ones = [
			"first",
			"second",
			"third",
			"fourth",
			"fifth",
			"sixth",
			"seventh",
			"eigth",
			"nineth"
		];

		const Tens = [
			"tenth" => 10,
			"eleventh" => 11,
			"twelfth" => 12,
			"thirteenth" => 13,
			"fourteenth" => 14,
			"fifteenth" => 15,
			"sixteenth" => 16,
			"seventeenth" => 17,
			"eighteenth" => 18,
			"nineteenth" => 19,
			"twentieth" => "twenty", /* 10th index */
			"thirtieth" => "thirty",
			"fourtieth" => "fourty",
			"fiftieth" => "fifty",
			"sixtieth" => "sixty",
			"seventieth" => "seventy",
			"eightieth" => "eighty",
			"ninetieth" => "ninety"
		];

		function __construct($model, $operation = Relation::Select) {

			$this->_model = $model;
			$this->_aquaTable = $model::aquaTable();
			$this->_connection = $model::connection();
			if ($operation == Relation::Select) {
				$this->_statement = new SelectStatement($this->_aquaTable);
			}
			else if ($operation == Relation::Insert) {
				$this->_statement = new InsertStatement($this->_aquaTable);
			}
			else if ($operation == Relation::Update) {
				$this->_statement = new UpdateStatement($this->_aquaTable);
			}
			else if ($operation == Relation::Delete) {
				$this->_statement = new DeleteStatement($this->_aquaTable);
			}
			else {
				throw new InvalidSqlOperationException();
			}

			$this->prependUnderscore();
			$this->readonly("aquaTable", "connection", "statement");
			$this->methodsAsProperties();

			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "defaultCallback");

		}

		function all() {
			return $this->_executeQuery();
		}

		function as($name) {

			$this->_statement->as($name);
			return $this;

		}

		protected function _buildJoinSequence($table, $type, $source = null) {

			if (is_array($table)) {

				// ["posts" => "comments"] => INNER JOIN posts ON posts.user_id = users.id INNER JOIN comments ON comments.post_id = posts.id
				// or
				// ["posts" => ["comments" => "guests"]] =>
				//	INNER JOIN posts ON posts.user_id = users.id INNER JOIN comments ON comments.post_id = posts.id INNER JOIN guests ON guests.comment_id = comments.id
				// ["posts" => ["comments" => "guests", "moderators"]] =>
				//	INNER JOIN posts ON posts.user_id = users.id INNER JOIN comments ON comments.post_id = posts.id INNER JOIN guests ON guests.comment_id = comments.id INNER JOIN moderators ON moderators.post_id = posts.id

				foreach ($table as $with => $subJoins) {

					if (is_numeric($with)) {

						$with = $subJoins;
						$subJoins = null;

					}

					$this->_statement->join($with, $type);
					$this->_setDefaultOnClause(empty($source) ? $this->_statement->relation->_name : $source, $with);

					if (!empty($subJoins)) {
						$this->join($subJoins, $type, $with);
					}

				}

			}
			else {

				$this->_statement->join($table, $type);
				$this->_setDefaultOnClause(empty($source) ? $this->_statement->relation->_name : $source, $table);

			}

		}

		function __call($method, $args = []) {
			return $this->defaultCallback($method, $args);
		}

		protected function defaultCallback($name, $args = []) {

			$index = static::getIndex($name);
			if ($index !== false && $index > -1) {

				$index -= 1;
				return $this->range($index, 1)->first;

			}
			else if (strpos($name, "findBy") === 0) {
				return $this->findByResolver(substr($name, strlen("findBy")), $args);
			}
			else {
				return $this->first->$name(...$args);
			}

		}

		function delete($clause, $params = []) {
			return $this->where($clause, $params);
		}

		function empty() {
			return $this->_executeQuery->empty;
		}

		function execute() {
			return $this->_executeQuery();
		}

		protected function _executeQuery() {

			if (empty($this->cache)) {

				$collections = $this->_connection->execute($this->_statement, [], $this->_fetchMode);
				if ($this->_fetchMode != 0) {
					return $this->cache = $collections;
				}

				if (is_a($this->_statement, SelectStatement::class)) {
					return $this->cache = $this->_tryBuildingObjects($collections);
				}

				$this->cache = $collections;

			}

			return $this->cache;

		}

		function exists($statement) {

			$this->_statement->exists($statement);
			return $this;

		}

		function findBy($column, $value) {

			$column = Helpers\NameHelper::getStorableName($column);

			$value = Relation::resolveSearchValue($value);
			if (!is_array($value)) {
				return $this->where(static::aquaTable()->$column->eq($value))->first;
			}
			else {
				return $this->where(static::aquaTable()->$column->in($value))->all;
			}

		}

		function findByResolver($stub, $values) {

			$matches = [];
			$offset = 0;
			$attributes = [];
			if (!is_array($values)) {
				$values = [$values];
			}

			if (preg_match_all("/[a-z0-9](And)[A-Z]/", $stub, $matches, PREG_OFFSET_CAPTURE)) {

				foreach ($matches[1] as $i => $match) {

					$attributeName = Helpers\NameHelper::getStorableName(substr($stub, $offset, $match[1] - $offset));
					$attributes[$attributeName] = $values[$i];
					$offset = $match[1] + strlen($match[0]);

				}

				$attributes[Helpers\NameHelper::getStorableName(substr($stub, $offset))] = $values[$i + 1];

			}
			else {
				$attributes[Helpers\NameHelper::getStorableName($stub)] = $values[0];
			}

			$resultSet = $this->where($attributes)->all;
			if ($resultSet->empty) {
				return false;
			}
			else {
				return $resultSet->first;
			}

		}

		function from($model) {

			$this->_statement->from($model::aquaTable());
			$this->_model = $model;
			return $this;

		}

		function fullJoin($with) {
			return $this->join($with, "FullJoin");
		}

		function groupBy($attribute) {

			$this->_statement->groupBy($attribute);
			return $this;

		}

		function includes($model) {

			$class = Helpers\NameHelper::classify($model, $this->_model);
			if (!class_exists($class)) {
				throw new ClassNotFoundException($class);
			}

			$this->_includes = [$class, $model];
			return $this;

		}

		function innerJoin($with) {
			return $this->join($with);
		}

		function insert($values = []) {

			if (empty($values)) {
				return $this;
			}

			foreach ($values as $name => $value) {

				// $this->_statement->insert($this->_aquaTable->$name->eq($value));
				if (is_null($value)) {
					$value = Types\Raw::sql("NULL");
				}

				$this->_statement->insert([$this->_aquaTable->$name, $value]);

			}

			return $this;

		}

		function join($table, $type = "InnerJoin") {

			$this->_buildJoinSequence($table, $type);
			// $this->_transformToModel = false;
			return $this;

		}

		function leftJoin($with) {
			return $this->join($with, "LeftJoin");
		}

		function new($params = []) {

			if (!is_a($this->_statement, SelectStatement::class)) {
				throw new Exception("Invalid use of Agility\\Data\\Relation::new()");
			}

			if ($this->_statement->where->empty()) {
				return null;
			}

			$booleanId = $this->_statement->where->root->left;
			if (!is_a($booleanId->left, "Aqua\\Attribute")) {
				return null;
			}

			$foreignKeyName = $booleanId->left->name;
			$value = $booleanId->right;
			$params[Str::pascalCase($foreignKeyName)] = $value;

			// If the root has a right and the table name matches, assume we are dealing with Polymorphic association
			if (!empty($this->_statement->where->root->right)) {

				$booleanType =  $this->_statement->where->root->right->left;
				if ($booleanId->left->table->name == $booleanType->left->table->name) {

					$foreignKeyName = $booleanType->left->name;
					$value = $booleanType->right;
					$params[Str::pascalCase($foreignKeyName)] = $value;

				}

			}

			return $this->_model::new($params);

		}

		function notExists($statement) {

			$this->_statement->exists($statement, false);
			return $this;

		}

		function on($clause) {

			$this->_statement->on($clause);
			return $this;

		}

		function order() {

			$sequences = func_get_args();
			foreach ($sequences as $seq) {

				if (is_array($seq)) {
					$this->_statement->order(new Order($seq[0], intval($seq[1] > 0)));
				}
				else {
					$this->_statement->order(new Order($seq, 1));
				}

			}

			return $this;

		}

		function pluck() {

			$this->_fetchMode = Connection\Base::FetchIndexedColumns;
			$this->_statement->unproject();

			return call_user_func_array([$this, "select"], func_get_args());

		}

		function range(int $offset, int $length = -1) {

			$this->_statement->range($offset, $length);
			return $this->_executeQuery();

		}

		protected function _resolveIncludes($objects) {

			if (empty($this->_includes)) {
				return;
			}

			$key = Inflect::singularize($this->_aquaTable->_name);
			$attribute = $key."Id";
			$key .= "_id";

			$includesName = $this->_includes[1];

			$relation = new Relation($this->_includes[0]);
			$resultSet = $relation->where($relation->aquaTable->$key->in($objects->map(":id")->all))->all;

			$objects->map(function($e) use ($includesName, $resultSet, $attribute) {

				$e->addSubObject($includesName, $resultSet->map(function ($r) use ($e, $attribute) {

					if ($e->id == $r->$attribute) {
						return $r;
					}

				}));

			});

		}

		// If the value being searched is an Agility Model, gets the value of the primary key;
		// or returns the value as it is.
		static function resolveSearchValue($value) {

			if (is_array($value) || is_a($value, Arrays::class)) {

				$return = [];
				foreach ($value as $each) {
					$return[] = static::resolveSearchValue($each);
				}

				return $return;

			}
			else {

				if (is_a($value, Model::class)) {
					$value = $value->valueOfPrimaryKey();
				}
				elseif (is_a($value, DateTime::class)) {
					$value = $value->format("Y-m-d H:i:s");
				}
				// elseif (is_null($value)) {
				// 	$value = Types\Raw::sql("NULL");
				// }

				return $value;

			}

		}

		function select() {

			$projections = func_get_args();
			if (count($projections) == 1 && is_array($projections[0])) {
				$projections = $projections[0];
			}

			foreach ($projections as $project) {
				$this->_statement->project($project);
			}

			return $this;

		}

		function _setDefaultOnClause($sourceTable, $referenceTable) {

			$foreignKeyName = Inflect::singularize($sourceTable)."_id";
			$this->_statement->on($this->sanitize($referenceTable).".".$this->sanitize($foreignKeyName)." = ".$this->sanitize($sourceTable).".`id`");

		}

		function skip(int $offset) {
			return $this->range($offset);
		}

		function take(int $length) {
			return $this->range(0, $length);
		}

		protected function _tryBuildingObjects($collections) {

			$collections = new Arrays($collections);
			if ($collections->empty) {
				return $collections;
			}

			$nativeAttributes = $collections[0]->toArray->keys;
			$modelAttributes = $this->_model::generatedAttributes()->keys;
			// if (!$nativeAttributes->diff($modelAttributes)->empty) {
			// 	return $collections;
			// }

			$objects = new Arrays;
			foreach ($collections as $collection) {

				$object = new $this->_model;
				$object->fillAttributes($collection, false, true);

				$objects[] = $object;

			}

			$this->_resolveIncludes($objects);
			return $objects;

		}

		function toSql() {
			return $this->_connection->toSql($this->_statement);
		}

		function update($attributes = []) {

			if (empty($attributes)) {
				return $this;
			}

			foreach ($attributes as $name => $value) {

				// $this->_statement->insert($this->_aquaTable->$name->eq($value));
				if (is_null($value)) {
					$value = Types\Raw::sql("NULL");
				}

				$this->_statement->set([$this->_aquaTable->$name, $value]);

			}

			return $this;

		}

		function where($clause, $params = []) {

			if (is_array($clause)) {

				foreach ($clause as $col => $value) {

					if (is_numeric($col)) {

						$params = Relation::resolveSearchValue($params);
						$this->_statement->where($value, $params);

					}
					else {

						$col = Str::snakeCase($col);

						$value = Relation::resolveSearchValue($value);
						if (is_array($value)) {
							$this->_statement->where($this->_aquaTable->$col->in($value));
						}
						elseif (is_null($value)) {
							$this->_statement->where($this->_aquaTable->$col->isNull());
						}
						else {
							$this->_statement->where($this->_aquaTable->$col->eq($value));
						}

					}

				}

			}
			else {

				$params = Relation::resolveSearchValue($params);
				$this->_statement->where($clause, $params);

			}

			return $this;

		}

	}

?>