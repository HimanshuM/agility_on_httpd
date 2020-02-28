<?php

namespace Agility\Generators\Exception;

use Exception;

	class GeneratorStubTakenException extends Exception {

		function __construct($message) {
			parent::__construct("Shorthand generator '$message' is already taken.");
		}

	}

?>