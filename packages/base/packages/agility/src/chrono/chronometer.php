<?php

namespace Agility\Chrono;

use AttributeHelper\Accessor;
use DateInterval;
use DateTime;
use DateTimeZone;
use JsonSerializable;

	class Chronometer extends DateTime implements JsonSerializable {

		use Accessor;

		protected $_precision = 0;

		function __construct() {

			$time = "now";
			$timezone = null;

			foreach (func_get_args() as $arg) {

				if (is_int($arg)) {
					$this->_precision = $arg;
				}
				else if (is_string($arg)) {
					$time = $arg;
				}
				else if (is_a($arg, DateTimeZone::class)) {
					$timezone = $arg;
				}

			}

			parent::__construct($time, $timezone);

			$this->methodsAsProperties();
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "__call");

		}

		protected function buildInterval($duration, $type) {

			$format = "P".$duration.$type[0];
			if (in_array($type, ["Hours", "Minutes", "Seconds"])) {
				$format = "PT".$duration.$type[0];
			}

			return new DateInterval($format);

		}

		function __call($method, $args = []) {

			$matches = [];
			if (preg_match('/(add|sub)(\d*)(Days|Hours|Minutes|Months|Seconds|Weeks|Years)/', $method, $matches)) {

				$method = $matches[1];

				if (empty($matches[2])) {
					$matches[2] = $args[0];
				}

				return $this->$method($this->buildInterval($matches[2], $matches[3]));

			}

		}

		static function createFromFormat($format, $time, $timezone = null) {

			$dateTime = parent::createFromFormat($format, $time, $timezone);
			return new Chronometer($dateTime->format("Y-m-d H:i:s"));

		}

		function date() {
			return $this->format("Y-m-d");
		}

		function __debugInfo() {
			return ["datetime" => $this->toIso8601()];
		}

		static function fromTimestamp($timestamp) {
			return (new Chronometer)->setTimestamp($timestamp);
		}

		function jsonSerialize() {
			return $this->toIso8601();
		}

		static function new($time) {
			return new Chronometer($time);
		}

		function timestamp() {
			return intval($this->format("U"));
		}

		function toIso8601() {
			return $this->format("c");
		}

		function tomorrow() {
			return (clone $this)->add1Day();
		}

		function __toString() {
			return $this->toIso8601();
		}

		function yesterday() {
			return (clone $this)->sub1Day();
		}

	}

?>