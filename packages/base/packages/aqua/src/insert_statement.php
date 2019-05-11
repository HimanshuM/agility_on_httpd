<?php

namespace Aqua;

use Exception;

	class InsertStatement extends Statement {

		protected $_values = [];
		protected $_valuesType = false;

		protected $_source = false;

		function __construct(Table $table = null) {

			parent::__construct();

			if (!empty($table)) {
				$this->_relation = $table;
			}

			$this->attributeReadOnly("values", "source", "valuesType");

		}

		function into(Table $table) {

			if (empty($table) && empty($this->_relation)) {
				throw new Exception("No table specified for into clause in InsertStatement", 1);
			}

			$this->_relation = $table;
			return $this;

		}

		function insert() {

			foreach (func_get_args() as $arg) {
				$this->_validateAndInsert($arg);
			}

			return $this;

		}

		function toSql(Visitors\AbstractVisitor $visitor) {

			if (empty($this->_relation)) {

				if (empty($this->_values)) {
					throw new Exception("Insert into table is not specified.", 1);
				}

				$first = $this->_values[0];
				if (is_array($first)) {
					$this->_relation = $first[0]->table;
				}
				else {
					$this->_relation = $first->table;
				}

			}

			return $visitor->compile($this);

		}

		function values(SelectStatement $statement) {

			if ($this->_valuesType == 1) {
				throw new Exception("Insert type is values, cannot switch to Select statement", 1);
			}

			$this->_valuesType = 2;
			$this->_source = $statement;

			return $this;

		}

		function where($clause, $params = []) {

		}

		private function _validateAndInsert($arg) {

			$this->_validateInsertType($arg);

			$this->_values[] = $arg;
			if (is_array($arg)) {
				$this->_valuesType = 1;
			}
			else {
				$this->_valuesType = 2;
			}

		}

		private function _validateInsertType($arg) {

			if (is_array($arg)) {

				if ($this->_valuesType == 2) {
					throw new Exception("Insert type is Select statement, cannot switch to values", 1);
				}
				$arg = $arg[0];

			}
			else if (is_a($arg, "Aqua\\Attribute") && $this->_valuesType == 1) {
				throw new Exception("Insert type is values, cannot switch to Select statement", 1);
			}

			$this->_validateDestinationAttributeType($arg);

			if (empty($this->_relation)) {
				$this->_relation = $arg->table;
			}
			else if ($this->_relation != $arg->table) {
				throw new Exception("Destination columns must belong to the same table", 1);
			}

		}

		private function _validateDestinationAttributeType($attr) {

			if (!is_a($attr, "Aqua\\Attribute")) {
				throw new Exception("Destination column can only by an object of type Aqua\\Attribute", 1);
			}

		}

	}

?>