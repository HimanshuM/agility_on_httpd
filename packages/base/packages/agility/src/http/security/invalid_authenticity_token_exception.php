<?php

namespace Agility\Http\Security;

use Exception;

	class InvalidAuthenticityTokenException extends Exception {

		function __construct($class, $method) {
			parent::__construct("Invalid authencity token for ".$class."::".$method."()");
		}

	}

?>