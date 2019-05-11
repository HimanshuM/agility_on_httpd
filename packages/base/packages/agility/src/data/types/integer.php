<?php

namespace Agility\Data\Types;

	class Integer extends Base {

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return intval($value);

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return intval($value);

		}

		function __toString() {
			return "integer";
		}

	}

?>