<?php

namespace Agility\Data\Relations;

use Agility\Data\Relation;
use Agility\Exceptions\InvalidArgumentTypeException;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use Closure;
use JsonSerializable;

	class Scope implements JsonSerializable {

		use Accessor;

		protected $_scopes;
		protected $_owner;
		protected $_externalObjectType;
		protected $_externalObject = null;
		protected $_externalObjectArgs = [];

		function __construct($type, $owner, $args = []) {

			$this->_scopes = new Arrays;
			$this->_owner = $owner;
			$this->_externalObjectType = $type;
			$this->_externalObjectArgs = $args;

			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "execute");

		}

		function add($name, $callback = null) {

			if (!is_a($callback, Closure::class) && !is_array($callback)) {
				throw new InvalidArgumentTypeException(static::class."::add()", 2, [Closure::class, "array"], get_class($callback));
			}

			if ($this->has($name)) {
				throw new ScopeAlreadyPresentException($name, $this->_owner);
			}

			$this->_scopes[$name] = $callback;

		}

		function __call($name, $args = []) {
			return $this->execute($name, $args);
		}

		function __debugInfo() {
			return $this->_externalObject->all->array;
		}

		protected function execute($name, $args = []) {

			$this->getExternalObject();

			if (!is_array($args)) {
				$args = [$args];
			}

			if (isset($this->_scopes[$name])) {

				$callback = $this->_scopes[$name];
				// A scope that is a has many association, will pass perform() with it's own object
				if (is_array($callback)) {
					$args = [$this];
				}
				else {
					$callback = $callback->bindTo($this, $this);
				}
				call_user_func_array($callback, $args);

				return $this;

			}
			else if (method_exists($this->_externalObject, $name)) {
				return call_user_func_array([$this->_externalObject, $name], $args);
			}
			else if (is_callable([$this->_externalObject, $name])) {
				return call_user_func_array([$this->_externalObject, $name], $args);
			}
			else {

				if (!empty($args)) {
					return $this->_externalObject->$name = $args[0];
				}

				return $this->_externalObject->$name;

			}

		}

		function getExternalObject() {

			if (empty($this->_externalObject)) {
				$this->_externalObject = new $this->_externalObjectType($this->_externalObjectArgs);
			}

			return $this->_externalObject;

		}

		function has($name) {
			return isset($this->_scopes[$name]);
		}

		function jsonSerialize() {
			return $this->_externalObject->all->array;
		}

		function restart() {
			$this->_externalObject = null;
		}

		function setExternalObject($externalObject) {
			$this->_externalObject = $externalObject;
		}

	}

?>