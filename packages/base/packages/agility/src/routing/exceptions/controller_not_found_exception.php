<?php

namespace Agility\Routing\Exceptions;

use Exception;

	class ControllerNotFoundException extends Exception {

		function __construct($controller) {
			parent::__construct("Controller $controller not found");
		}

	}

?>