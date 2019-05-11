<?php

namespace Agility\Data\Types;

	class Enum extends Base {

		protected $values = [];

		function __construct($values = null) {

			parent::__construct();

			if (!empty($values)) {

				$values = explode(",", $values);
				foreach ($values as $value) {
					$this->values[] = "\"".trim($value)."\"";
				}

			}

		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return $value;

		}

		function options() {
			return ["values" => "[".implode(", ", $this->values)."]"];
		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return $value;

		}

		function setParameters($params = []) {

			parent::setParameters($params);
			if (!empty($params["values"])) {
				$this->values = $params["values"];
			}

		}

		function __toString() {
			return "enum";
		}

		function valuesString() {
			return implode(", ", array_map(function($e) { return "'".$e."'"; }, $this->values));
		}

	}

?>