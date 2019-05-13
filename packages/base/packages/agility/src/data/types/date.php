<?php

namespace Agility\Data\Types;

use Agility\Chrono;

	class Date extends Base {

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			if (!is_a($value, Chrono\Date::class)) {

				if (is_a($value, Chrono\Chronometer::class)) {
					return (Chrono\Date)$value;
				}

				return new Chrono\Date($value);

			}

			return $value;

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return $value->date;

		}

		function __toString() {
			return "date";
		}

	}

?>