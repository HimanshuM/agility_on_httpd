<?php

namespace Aqua;

use Exception;

	trait Accessor {

		protected $_accessibles = [];
		protected $_readOnlys = [];

		function attributeAccessible() {

			foreach (func_get_args() as $attribute) {

				if (!in_array($attribute, $this->_accessibles)) {
					$this->_accessibles[] = $attribute;
				}

				if (in_array($attribute, $this->_readOnlys)) {
					$this->_readOnlys = array_diff($this->_readOnlys, [$attribute]);
				}

			}

		}

		function attributeReadOnly() {

			foreach (func_get_args() as $attribute) {

				if (!in_array($attribute, $this->_readOnlys)) {
					$this->_readOnlys[] = $attribute;
				}

				if (in_array($attribute, $this->_accessibles)) {
					$this->_accessibles = array_diff($this->_accessibles, [$attribute]);
				}

			}

		}

		function __get($attribute) {

			$property = "_".$attribute;

			if (!isset($this->$property) && !is_null($this->$property)) {
				throw new Exception("Invalid property '$attribute' for class '".get_called_class()."'", 1);
			}

			return $this->$property;

		}

		function __set($attribute, $value) {

			if (in_array($attribute, $this->_readOnlys)) {
				throw new Exception("Cannot modify read only property '$attribute'", 1);
			}

			$property = "_".$attribute;
			$this->$property = $value;

		}

		function __isset($attribute) {

			if (!in_array($attribute, $this->_accessibles) && !in_array($attribute, $this->_readOnlys)) {
				return false;
			}

			$property = "_".$attribute;
			return !empty($this->$property);

		}

	}

?>