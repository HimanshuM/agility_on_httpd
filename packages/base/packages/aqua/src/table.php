<?php

namespace Aqua;

use Exception;

	class Table {

		use Accessor {
			__get AS __getAccessor;
		}

		private $_name;
		private $_alias;

		function __construct($table) {

			if (empty($table)) {
				throw new Exception("Table name cannot be empty", 1);
			}

			$this->_name = $table;

			$this->attributeReadOnly("_name", "_alias");

		}

		function __get($attribute) {

			if ($attribute == "_name" || $attribute == "_alias") {
				return $this->__getAccessor(substr($attribute, 1));
			}

			return new Attribute($attribute, $this);

		}

		function __isset($attribute) {
			return !empty($this->$attribute);
		}

		function alias($name = null) {

			if (is_null($name)) {
				$name = $this->_name."_2";
			}
			else if (!is_string($name)) {
				throw new Exception("Table alias can only be a string.", 1);
			}

			// Temporary soln...
			$alias = clone $this;
			$alias->_alias = $name;

			return $alias;
			// Temporary soln...

		}

		function delete() {

			$delete = new DeleteStatement($this);

			if (func_num_args() > 1) {
				return call_user_func_array([$delete, "from"], func_get_args());
			}
			else {
				return $delete;
			}

		}

		function describe() {
			return new DescribeStatement($this);
		}

		function insert() {

			$insert = new InsertStatement($this);

			if (func_num_args() > 1) {
				return call_user_func_array([$insert, "insert"], func_get_args());
			}
			else {
				return $insert;
			}

		}

		function from() {
			return new SelectStatement($this);
		}

		function project() {

			$projections = [];
			foreach (func_get_args() as $arg) {

				if (!is_a($arg, "Aqua\\Attribute")) {
					$projections[] = new Attribute($arg, $this);
				}
				else {
					$projections[] = $arg;
				}

			}

			return $this->from()->project($projections);

		}

		function update() {

			$update = new UpdateStatement($this);

			if (func_num_args() > 1) {
				return call_user_func_array([$update, "set"], func_get_args());
			}
			else {
				return $update;
			}

		}

		function where($clause, $params = []) {

			if (!is_string($clause) && !is_a($clause, "Aqua\\Node")) {
				throw new Exception("Where clause should either be a string or an instance of class Aqua\\Node", 1);
			}

			return $this->from()->where(Node::build($clause));

		}

	}

?>