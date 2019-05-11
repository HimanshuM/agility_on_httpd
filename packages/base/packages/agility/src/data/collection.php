<?php

namespace Agility\Data;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use Iterator;
use JsonSerializable;
use Phpm\Exceptions\ClassExceptions\SerializationException;
use Phpm\Exceptions\PropertyExceptions\PropertyNotFoundException;
use Serializable;
use StringHelpers\Str;

	class Collection implements Iterator, Serializable, JsonSerializable {

		use Accessor;

		protected $_attributes;
		protected $_storageMapping = [];

		protected $_pointer = 0;

		protected $_className = "";

		function __construct($className = "") {

			$this->_attributes = new Arrays();
			$this->_className = $className;

			$this->methodsAsProperties("toArray", "toStorableArray");
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "attributeAccessor");

		}

		function attributeAccessor($name, $value = nil) {

			if ($value === nil) {
				return $this->_getAttribute($name);
			}

			$this->_setAttribute($name, $value);

		}

		function current() {
			return $this->_attributes[$this->_storageMapping[$this->_pointer]];
		}

		function __debugInfo() {
			return $this->_attributes->all;
		}

		private function _getAttribute($attribute) {

			if (empty($this->_storageMapping[$attribute])) {
				throw new PropertyNotFoundException($attribute, $this->_className ?: Collection::class);
			}

			return $this->_attributes[$attribute];

		}

		protected function _getStorableName($attribute) {
			return Str::snakeCase($attribute);
		}

		function has($key) {
			return $this->_attributes->exists($key);
		}

		function __isset($attribute) {
			return $this->_attributes->exists($attribute) ? $this->_attributes[$attribute] : false;
		}

		function jsonSerialize() {
			return $this->_attributes;
		}

		function key() {
			return $this->_attributes->keys[$this->_pointer];
		}

		function next() {
			$this->_pointer++;
		}

		protected function _normalize($attribute) {
			return Str::pascalCase($attribute);
		}

		function rewind() {
			$this->_pointer = 0;
		}

		function serialize() {
			return serialize($this->_attributes);
		}

		private function _setAttribute($attribute, $value) {

			// We are most probably dealing with initialization of the object
			if (empty($this->_storageMapping[$attribute])) {

				$storableName = $this->_getStorableName($attribute);
				$attribute = $this->_normalize($attribute);
				$this->_storageMapping[$attribute] = $storableName;

			}

			$this->_attributes[$attribute] = $value;

		}

		function toArray() {
			return $this->_attributes;
		}

		function toStorableArray() {

			$array = [];
			foreach ($this->_attributes as $attribute => $value) {
				$array[$this->_storageMapping[$attribute]] = $value;
			}

			return new Arrays($array);

		}

		function unserialize($attributes) {

			if (($unserialized = unserialize($attributes)) === false) {
				throw new SerializationException(1);
			}

			$this->__construct();
			foreach ($unserialized as $attribute => $value) {
				$this->__set($attribute, $value);
			}

		}

		function valid() {
			return $this->_pointer < count($this->_attributes);
		}

	}

?>