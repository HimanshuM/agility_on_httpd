<?php

namespace Workflow\Exceptions;

use Exception;

	class InvalidEventException extends Exception {

		function __construct($event, $state, $class) {
			parent::__construct("State '$state' of class $class has no event '$event'");
		}

	}

?>