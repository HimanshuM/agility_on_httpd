<?php

namespace Phpm\Exceptions\ClassExceptions;

use Exception;

	class MethodNotFoundException extends Exception {

		function __construct($class, $method) {
			parent::__construct("Method '$method' does not exists on class $class");
		}

	}

?>