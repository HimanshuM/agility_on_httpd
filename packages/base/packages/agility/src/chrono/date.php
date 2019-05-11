<?php

namespace Agility\Chrono;

	class Date extends Chronometer {

		function __construct() {

			$time = "now";
			$timezone = null;

			foreach (func_get_args() as $arg) {

				if (is_string($arg)) {
					$time = $arg;
				}
				else if (is_a($arg, DateTimeZone::class)) {
					$timezone = $arg;
				}

			}

			parent::__construct($time, $timezone);
			$this->setTime(0, 0, 0);

		}

		function __debugInfo() {
			return ["date" => $this->date];
		}

		function jsonSerialize() {
			return $this->date();
		}

		function __toString() {
			return $this->date;
		}

	}

?>