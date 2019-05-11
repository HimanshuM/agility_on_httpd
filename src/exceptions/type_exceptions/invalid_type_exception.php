<?php

namespace Phpm\Exceptions\TypeExceptions;

use Exception;
use StringHelpers\Inflect;

	class InvalidTypeException extends Exception {

		function __construct($object, $expected) {
			parent::__construct($object." can only be of type ".Inflect::toSentence($expected).".");
		}

	}

?>