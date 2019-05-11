<?php

namespace Phpm\Exceptions\MethodExceptions;

use Exception;
use StringHelpers\Inflect;

	class InvalidArgumentTypeException extends Exception {

		function __construct($method, $position, $expected, $actual = false) {
			parent::__construct("Argument $position passed to $method must be an instance of ".Inflect::toSentence($expected, "or").($actual ? ", $actual given" : ""));
		}

	}

?>