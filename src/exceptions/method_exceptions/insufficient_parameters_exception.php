<?php

namespace Phpm\Exceptions\MethodExceptions;

use Exception;

	class InsufficientParametersException extends Exception {

		function __construct($methodName, $totalArgs, $givenArgs) {
			parent::__construct($methodName." expects $totalArgs arguments, $givenArgs found");
		}

	}

?>