<?php

namespace Aqua;

use Exception;

	class Attribute {

		use Accessor;
		use Functions;

		private $_table;
		private $_name;
		private $_value = null;
		private $_as = null;

		static $attributesCache = [];

		function __construct($name, $table) {

			if (empty($name)) {
				throw new Exception("Attribute name cannot be empty", 1);
			}
			if (!($table instanceof Table)) {
				throw new Exception("table must be an object of class Table", 1);
			}

			$this->_table = $table;
			$this->_name = $name;

			$this->attributeReadOnly("table", "name", "value", "as");

		}

		function as($alias) {

			if (!is_string($alias)) {
				throw new Exception("Attribute alias should be of type string, '".get_class($alias)."' given", 1);
			}

			$this->_as = $alias;

			return $this;

		}

		function asc() {
			return new Order($this, 1);
		}

		function desc() {
			return new Order($this, -1);
		}

		function eq($value) {
			return $this->_setValue($value, "Equal");
		}

		function notEq($value) {
			return $this->_setValue($value, "NotEqual");
		}

		function gt($value) {
			return $this->_setValue($value, "GreaterThan");
		}

		function gtEq($value) {
			return $this->_setValue($value, "GreaterThanEqual");
		}

		function lt($value) {
			return $this->_setValue($value, "LessThan");
		}

		function ltEq($value) {
			return $this->_setValue($value, "LessThanEqual");
		}

		function in($value) {

			if (!is_array($value)) {
				$value = [$value];
			}

			return $this->_setValue($value, "In");
		}

		function notIn($value) {

			if (!is_array($value)) {
				$value = [$value];
			}

			return $this->_setValue($value, "NotIn");

		}

		function isNull() {
			return $this->_setValue(null, "Is");
		}

		function isNotNull() {
			return $this->_setValue(null, "IsNot");
		}

		function like($value) {
			return $this->_setValue($value, "Like");
		}

		private function _setValue($value, $operator = null) {

			if (!is_null($operator)) {
				return new Binary($this, $value, $operator);
			}

		}

	}

?>