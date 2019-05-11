<?php

namespace Agility\Data\Types;

	class Json extends Base {

		function __construct() {
			parent::__construct();
		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			if (is_string($value)) {
				return json_decode($value, true);
			}

			return $value;

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return json_encode($value);

		}

		function __toString() {
			return "json";
		}

	}

?>