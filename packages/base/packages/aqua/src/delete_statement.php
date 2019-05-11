<?php

namespace Aqua;

	class DeleteStatement extends Statement {

		protected $_joins = [];
		protected $_aliasTable = null;

		function __construct(Table $table = null) {

			parent::__construct();
			$this->_relation = [];

			if (!empty($table)) {
				$this->_relation[] = $table;
			}

			$this->attributeReadOnly("updates", "joins", "aliasTable");

		}

		function as($name) {

			$lastJoin = $this->_joins[count($this->_joins) - 1];

			if (is_a($lastJoin->with, "Aqua\\Table")) {
				$lastJoin->with->alias = $name;
			}
			else {
				$lastJoin->with->createAliasTable($name);
			}

			$lastJoin->as = $name;

			return $this;

		}

		function from(Table $table) {

			$this->_relation[] = $table;
			return $this;

		}

		function fullJoin($with) {
			return $this->join($with, "FullJoin");
		}

		function innerJoin($with) {
			return $this->join($with);
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

		function leftJoin($with) {
			return $this->join($with, "LeftJoin");
		}

		function on(Binary $clause) {

			$lastJoin = $this->_joins[count($this->_joins) - 1];
			$lastJoin->on = new On((new Where)->append($clause));

			return $this;

		}

		private function _validateSetParam($arg) {

			if (!is_array($arg)) {
				throw new Exception("Parameters to UpdateStatement::set() should be of type array.", 1);
			}

			if (!is_a($arg[0], "Aqua\\Attribute")) {
				throw new Exception("Destination column should be an object of type Aqua\\Attribute", 1);
			}

			$this->_updates[] = $arg;

		}

	}

?>