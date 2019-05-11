<?php

namespace Agility\Routing\Exceptions;

use Exception;

	class UndefinedRouteActionException extends Exception {

		function __construct($route) {
			parent::__construct("Route '$route' does not have an handler action defined");
		}

	}

?>