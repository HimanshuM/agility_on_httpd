<?php

namespace Agility\Data\Types;

	class UInt extends Integer {

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return abs($value);

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return abs($value);

		}

		function __toString() {
			return "uint";
		}

	}

?>