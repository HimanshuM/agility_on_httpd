<?php

namespace Agility\Data\Schema;

use Agility\Data\Schema\Attribute;
use Agility\Data\Types\Base;
use ArrayUtils\Arrays;
use StringHelpers\Str;

	trait Builder {

		static function accessibleAttributes() {
			return static::metaStore()->accessibleAttributes;
		}

		protected static function attribute($name, $type, $default = null) {

			$attribute = new Attribute($name, Base::getType($type));
			if ($default !== null && $default !== false) {
				$attribute->defaultValue = $default;
			}

			static::attributeObjects()[$name] = $attribute;

		}

		protected static function attributeObjects() {
			return static::metaStore()->attributeObjects;
		}

		protected static function attrAccessible() {

			foreach (func_get_args() as $attribute) {

				if (in_array($attribute, static::protectedAttributes()->array)) {
					throw new Exception("'$attribute' has already been marked protected", 1);
				}

				static::accessibleAttributes()[] = $attribute;

			}

		}

		protected static function attrProtected() {

			foreach (func_get_args() as $attribute) {

				if (in_array($attribute, static::accessibleAttributes()->array)) {
					throw new Exception("'$attribute' has already been marked mass accessible", 1);
				}

				static::protectedAttributes()[] = $attribute;

			}

		}

		protected static function buildAttribute($attribute) {

			$dataType = Attribute::parseDataType(static::connection()->getTypeMapper(), $attribute->type);

			$unique = $attribute->key == "UNI";
			$index = $attribute->key == "MUL";
			$autoIncrement = strpos($attribute->extra, "auto_increment") !== false;
			$nullable = $attribute->null != "NO";

			$onUpdate = null;
			if (strpos($attribute->extra, "on update") !== false) {

				$fragments = explode(" ", $attribute->extra);
				foreach ($fragments as $i => $fragment) {

					if ($fragment == "update") {

						$onUpdate = $fragments[$i + 1];
						break;

					}

				}

			}

			static::metaStore()->generatedAttributes[Str::pascalCase($attribute->field)] = new Attribute($attribute->field, $dataType, $nullable, $attribute->default, $autoIncrement, $index, $unique, $onUpdate);

		}

		protected static function generateAttributes() {

			if (!static::metaStore()->generatedAttributes->empty) {
				return;
			}

			$resultSet = static::connection()->execute(static::aquaTable()->describe());
			foreach ($resultSet as $attribute) {
				static::buildAttribute($attribute);
			}

		}

		static function generatedAttributes() {

			if (static::metaStore()->generatedAttributes->empty) {
				static::generateAttributes();
			}

			return static::metaStore()->generatedAttributes;

		}

		protected static function json() {

			foreach (func_get_args() as $attribute) {
				static::attribute($attribute, "Json");
			}

		}

		protected static function protectedAttributes() {
			return static::metaStore()->protectedAttributes;
		}

		protected static function serialize() {

			foreach (func_get_args() as $attribute) {
				static::attribute($attribute, "serialized");
			}

		}

	}

?>