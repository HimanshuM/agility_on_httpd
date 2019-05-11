<?php

namespace Agility\Data\Types;

	class Boolean extends Base {

		function __construct() {
			parent::__construct();
		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return boolval($value);

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return abs($value);

		}

		function __toString() {
			return "boolean";
		}

	}

?>