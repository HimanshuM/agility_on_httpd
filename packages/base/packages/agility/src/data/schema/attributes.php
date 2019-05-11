<?php

namespace Agility\Data\Schema;

use Agility\Data\Collection;
use Agility\Data\Exceptions\AttributeDoesNotExistException;
use Agility\Data\Model;
use Agility\Data\Relation;
use Agility\Data\Relations\Scope;
use Agility\Exceptions;
use ArrayUtils\Arrays;
use InvalidArgumentException;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;

	trait Attributes {

		protected $attributes;

		function addSubObject($name, $object) {
			$this->attributes->$name = $object;
		}

		function fetchAttributes($noCasting = true) {

			if ($noCasting) {
				return $this->attributes->toArray;
			}

			$return = [];

			// OLD LOGIC
			// FLAW: If a random attribute was added to the object which does not exist in the table,
			// save() would still try to write it to the table, which would fail
			$collection = $this->attributes->toArray;
			foreach ($collection as $name => $value) {

				if (static::generatedAttributes()[$name]->onUpdate == "CURRENT_TIMESTAMP") {
					continue;
				}

				if (is_a($value, Model::class)) {
					$value = $value->valueOfPrimaryKey();
				}
				else if (is_a($value, Relation::class) || is_a($value, Scope::class)) {

					$value = $value->first;
					if (empty($value)) {
						$value = null;
					}
					else if (is_a($value, Model::class)) {
						$value = $value->valueOfPrimaryKey();
					}
					else {
						throw new InvalidTypeException(static::class."::$name", static::generatedAttributes()[$name]->dataType);
					}

				}

				if (isset(static::attributeObjects()[$name])) {
					$value = static::attributeObjects()[$name]->dataType->serialize($value);
				}
				else if (static::generatedAttributes()->exists($name)) {
					$value = static::generatedAttributes()[$name]->dataType->serialize($value);
				}
				else {
					// We do not return a key which does not exist in the table
					continue;
				}

				$name = static::generatedAttributes()[$name]->name;

				$return[$name] = $value;

			}

			return $return;

		}

		function fillAttributes($collection, $forcible = true, $skipNotFound = false) {

			if (is_a($collection, Collection::class)) {
				$collection = $collection->toArray;
			}
			else if (!is_array($collection) && !is_a($collection, Arrays::class)) {
				throw new InvalidArgumentException("Array or an object of type Agility\\Data\\Collection is expected", 1);
			}

			foreach ($collection as $name => $value) {

				if ($forcible !== false) {

					if (!in_array($name, static::accessibleAttributes()->array)) {
						// throw new BatchUpdateException($name, static::class);
						$this->errors[$name] = "'$name' cannot be batch updated";
					}

				}
				else {
					$this->_fresh = false;
				}

				if (is_a($value, Model::class)) {
					$value = $value->valueOfPrimaryKey();
				}
				else if (is_a($value, Relation::class) || is_a($value, Scope::class)) {

					$value = $value->first;
					if (empty($value)) {
						$value = null;
					}
					else if (is_a($value, Model::class)) {
						$value = $value->valueOfPrimaryKey();
					}
					else {
						throw new InvalidTypeException(static::class."::$name", static::generatedAttributes()[$name]->dataType);
					}

				}

				if (isset(static::attributeObjects()[$name])) {
					$value = static::attributeObjects()[$name]->dataType->unserialize($value);
				}
				else if (static::generatedAttributes()->exists($name)) {
					$value = static::generatedAttributes()[$name]->dataType->unserialize($value);
				}
				else if ($skipNotFound) {
					continue;
				}
				else {
					throw new AttributeDoesNotExistException($name, static::class);
				}

				$this->attributes->$name = $value;

			}

			foreach (static::attributeObjects() as $key => $value) {

				if (!$this->attributes->has($key)) {
					$this->attributes->$key = $value->dataType->unserialize($value->defaultValue);
				}

			}

			if ($forcible === false) {
				$this->_runCallbacks("afterFind");
			}

		}

		private function _getAttribute($name) {

			// We do not need the below check, because, if an attribute was added which does not exist in the table,
			// we still need to return that attribute
			// if (!$this->_hasAttribute($name)) {
			// 	return null;
			// }

			if (!$this->attributes->has($name)) {

				$attribute = static::generatedAttributes()[$name];
				$this->attributes->$name = $attribute->defaultValue;

			}

			return $this->attributes->$name;

		}

		private function _hasAttribute($name) {
			return static::generatedAttributes()->exists($name);
		}

		function isSet($attribute) {
			return isset($this->attributes->$attribute) ?: property_exists($this, $attribute);
		}

		private function _setAttribute($name, $value) {

			if (isset(static::attributeObjects()[$name])) {
				$value = static::attributeObjects()[$name]->dataType->cast($value);
			}

			if (!$this->attributes->has($name) || ($this->attributes->has($name) && $this->attributes->$name != $value)) {

				$this->attributes->$name = $value;
				$this->_dirty = true;

			}

		}

	}

?>