<?php

namespace Agility\Data\Types;

use StringHelpers\Str;

	abstract class Base {

		protected $limit = null;
		protected $precision = null;
		protected $scale = null;

		protected static $registeredTypes = [];

		const DeferredTypes = [
			"string" => "str",
			"int" => "integer",
			"uint" => "u_int",
			"datetime" => "datetime_db"
		];

		const ValidTypes = [
			"binary" => '/binary(\[\d+\])?/',
			"boolean" => '/bool/',
			"datetime" => '/datetime(\[\d+\])/',
			"date" => '/date/',
			"double" => '/double(\[\d+(,\s*\d+)?\])?/',
			"enum" => '/enum(\[[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(,[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\])?/',
			"float" => '/float(\[\d+(,\d+)?\])?/',
			"integer" => '/int(eger)?(\[\d+\])?/',
			"reference" => '/references(\[\w+\])?/',
			"str" => '/string(\[\d+\])?/',
			"text" => '/text(\[\d+\])?/',
			"timestamp" => '/timestamp(\[\d+\])/',
			"uint" => '/uint(\[\d+\]?)/'
		];

		function __construct() {

		}

		// User for cast objects from setters
		abstract function cast($value);

		static function getType($name, $size = null) {

			if (isset(Base::DeferredTypes[$name])) {
				$name = Base::DeferredTypes[$name];
			}

			if (isset(Base::$registeredTypes[$name])) {

				$className = Base::$registeredTypes[$name];
				return new $className($size);

			}
			else if (file_exists(__DIR__."/$name.php")) {

				$name = Str::camelCase($name);
				$name = "\\Agility\\Data\\Types\\".$name;
				return new $name($size);

			}
			else {
				throw new SqlTypeNotFoundException($name);
			}

		}

		function nativeType($typeMapper) {
			return $typeMapper->getNativeType($this->__toString(), $this->limit, $this->precision, $this->scale);
		}

		function options() {

			return [
				"limit" => $this->limit,
				"precision" => $this->precision,
				"scale" => $this->scale
			];

		}

		static function raw($string) {
			return new RawString($string);
		}

		static function register($className, $typeName = false) {

			if (empty($typeName)) {
				$typeName = Str::pascalCase(Str::componentName($className));
			}

			// if (!in_array($typeName, Base::$registeredTypes)) {
				Base::$registeredTypes[$typeName] = $className;
			// }

		}

		// Used for casting objects to database types
		abstract function serialize($value);

		function setParameters($params = []) {

			if (!empty($params["limit"])) {
				$this->limit = $params["limit"];
			}
			if (!empty($params["precision"])) {
				$this->precision = $params["precision"];
			}
			if (!empty($params["scale"])) {
				$this->scale = $params["scale"];
			}

		}

		abstract function __toString();

		// Used for casting objects from database types
		function unserialize($value) {
			return $this->cast($value);
		}

	}

?>