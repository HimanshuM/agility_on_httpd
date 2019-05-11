<?php

namespace Agility\Data\Helpers;

use Agility\Data\Validations;
use ArrayUtils\Arrays;
use Phpm\Exceptions\PropertyExceptions\PropertyNotFoundException;

	trait Dispatch {

		protected static $_registeredFallbackCallable;

		protected function defaultCallback($name, $value = nil) {

			if ($this->_hasAttribute($name)) {

				if ($value === nil) {
					return $this->_getAttribute($name);
				}
				else {
					return $this->_setAttribute($name, $value);
				}

			}
			else if (static::belongsToAssociations()->exists($name)) {
				return $this->belongsToAssociations()[$name]->fetch($this);
			}
			else if (static::hasManyAssociations()->exists($name)) {
				return static::hasManyAssociations()[$name]->prepare($this);
			}
			else if (static::hasAndBelongsToManyAssociations()->exists($name)) {
				return static::hasManyAssociations()[$name]->prepare($this)->first;
			}
			else if (static::hasOneAssociations()->exists($name)) {
				return static::hasOneAssociations()[$name]->prepare($this)->first;
			}
			else {
				throw new PropertyNotFoundException($name, static::class);
			}

		}

		function __call($name, $args = []) {

			if ($this->_hasAttribute($name)) {

				if (empty($args)) {
					return $this->_getAttribute($name);
				}
				else {
					return $this->_setAttribute($name, $args[0]);
				}

			}
			else if (static::$_registeredFallbackCallable->exists(static::class)) {

				$callable = static::$_registeredFallbackCallable[static::class];
				return $this->$callable($name, $args);

			}

		}

		static function __callStatic($name, $args = []) {

			if (strpos($name, "findBy") === 0) {
				return static::findByResolver(substr($name, strlen("findBy")), $args);
			}
			else if (in_array($name, static::$CALLBACKS)) {
				return static::_addToCallbacks($name, $args);
			}
			else if (($validator = Validations\Base::isAvailable($name, true)) !== false) {

				if (count($args) == 2 && is_array($args[1])) {

					if (is_array($args[0])) {

						foreach ($args[0] as $attr) {
							static::validates($attr, $validator, $args[1]);
						}

					}
					else {
						static::validates($args[0], $validator, $args[1]);
					}

				}
				else {

					foreach ($args as $attr) {
						static::validates($attr, $validator);
					}

				}

			}
			else if (static::hasScope($name)) {
				return static::tryScope($name, $args);
			}

		}

		protected static function registerFallbackCallable($name) {

			if (empty(static::$_registeredFallbackCallable)) {
				static::$_registeredFallbackCallable = new Arrays;
			}

			static::$_registeredFallbackCallable[static::class] = $name;

		}

		function valueOfPrimaryKey() {

			$primaryKey = static::$primaryKey;
			return $this->$primaryKey;

		}

	}

?>