<?php

namespace Agility\Data\Types;

	class Serialized extends Base {

		function __construct() {
			parent::__construct();
		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			if (is_string($value)) {
				return unserialize($value);
			}

			return $value;

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return serialize($value);

		}

		function __toString() {
			return "text";
		}

	}

?>