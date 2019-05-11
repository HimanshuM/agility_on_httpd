<?php

namespace Agility\Data\Types;

	class Double extends Base {

		function __construct($size = null) {

			parent::__construct();

			if (!empty($size)) {

				if (is_string($size)) {
					$size = explode(",", $size);
				}
				else if (is_numeric($size)) {
					$size = [intval($size)];
				}
				else if (!is_array($size)) {
					throw new Exception("Size for Double can only be numeric, numeric array, or comma-delimited numeric string", 1);
				}

				if (count($size) == 1) {
					$this->scale = $size[0];
				}
				else {

					$this->precision = $size[0];
					$this->scale = $size[1];

				}

			}

		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return floatval($value);

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return floatval($value);

		}

		function __toString() {
			return "double";
		}

	}

?>