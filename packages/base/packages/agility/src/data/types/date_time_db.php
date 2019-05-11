<?php

namespace Agility\Data\Types;

use Agility\Chrono\Chronometer;

	class DateTimeDb extends Base {

		const CurrentTimestamp = "CURRENT_TIMESTAMP";

		function cast($value) {

			if (empty($value)) {
				return null;
			}

			if (!is_a($value, Chronometer::class)) {

				$object = Chronometer::new($value);
				return $object;

			}

			return $value;

		}

		function serialize($value) {

			if (empty($value)) {
				return null;
			}

			if ($value == DatetimeDb::CurrentTimestamp) {
				return $value;
			}
			else if (is_a($value, RawString::class)) {
				return $value;
			}

			$format = "Y-m-d H:i:s";
			if (!empty($this->precision)) {

				if ($this->precision == "3") {
					$format .= ".v";
				}
				else {
					$format .= ".u";
				}

			}

			return $value->format($format);

		}

		function __toString() {
			return "datetime";
		}

	}

?>