<?php

namespace Aqua;

use Exception;

	class SelectStatement extends Statement {

		use Accessor {
			__get AS __getAccessor;
		}

		protected $_projections = [];

		protected $_joins = [];

		protected $_groupings = [];
		protected $_order = [];
		protected $_limit = null;

		protected $_aliasTable = null;

		function __construct(Table $from) {

			parent::__construct();

			$this->from($from);

			$this->attributeReadOnly("projections", "joins", "groupings", "order", "limit", "aliasTable");

		}

		function __get($attribute) {

			if (in_array($attribute, $this->_readOnlys)) {
				return $this->__getAccessor($attribute);
			}

			return $this->_aliasTable->$attribute;

		}

		function from(Table $table) {

			if (empty($table)) {
				throw new Exception("Invalid from supplied to SelectManager", 1);
			}

			$this->_relation = $table;

			return $this;

		}

		function project() {

			$projections = func_get_args();
			if (count($projections) == 1 && is_array($projections[0])) {
				$projections = $projections[0];
			}

			foreach ($projections as $project) {

				if (is_string($project)) {
					$project = new SqlString($project);
				}
				else if (!is_a($project, Attribute::class)) {
					throw new Exception("Projection must be an object of class Attribute, ".get_class($project)." given", 1);
				}

				$this->_insertProjection($project);

			}

			return $this;

		}

		function unproject() {

			$this->_projections = [];
			return $this;

		}

		function join($table, $type = "InnerJoin") {

			if (empty($table)) {
				throw new Exception("Cannot build join with empty table", 1);
			}

			$type = "Aqua\\".$type;
			$join = new $type($table);
			$this->_joins[] = $join;

			return $this;

		}

		function innerJoin($with) {
			return $this->join($with);
		}

		function leftJoin($with) {
			return $this->join($with, "LeftJoin");
		}

		function fullJoin($with) {
			return $this->join($with, "FullJoin");
		}

		function on($clause) {

			if (!is_string($clause) && !is_a($clause, Binary::class)) {
				throw new Exception("Where clause should either be a string or an instance of class Aqua\\Binary", 1);
			}

			$lastJoin = $this->_joins[count($this->_joins) - 1];
			$lastJoin->on = new On((new Where)->append(Node::build($clause)));

			return $this;

		}

		function as($name) {

			$lastJoin = $this->_joins[count($this->_joins) - 1];

			if (is_a($lastJoin->with, Table::class)) {
				$lastJoin->with->alias = $name;
			}
			else {
				$lastJoin->with->createAliasTable($name);
			}

			$lastJoin->as = $name;

			return $this;

		}

		function exists(SelectStatement $statement) {

			$this->_where->append(new Exists($statement));
			return $this;

		}

		function notExists(SelectStatement $statement) {

			$this->_where->append(new Exists($statement, false));
			return $this;

		}

		function groupBy($attribute) {

			if (is_string($attribute)) {
				$attribute = new SqlString($attribute);
			}
			else if (!is_a($attribute, Attribute::class)) {
				throw new Exception("Group by attribute should either be a string or an instance of class Aqua\\Attribute", 1);
			}

			$this->_groupings[] = $attribute;
			return $this;

		}

		function order(Order $seq) {

			$this->_order[] = $seq;
			return $this;

		}

		function skip(int $offset) {
			return $this->range($offset);
		}

		function take(int $length) {
			return $this->range(0, $length);
		}

		function range(int $offset, int $length = -1) {

			$this->_limit = new Limit($offset, $length);
			return $this;

		}

		private function createAliasTable($name) {
			$this->_aliasTable = new Table($name);
		}

		private function _insertProjection($project) {

			if (!in_array($project, $this->_projections)) {
				$this->_projections[] = $project;
			}

		}

	}

?>