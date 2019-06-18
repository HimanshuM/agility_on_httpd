<?php

namespace Agility\Http\Security;

use Exception;

	class CsrfTokenNameEmptyException extends Exception {

		function __construct() {
			parent::__construct("CSRF token name cannot be empty");
		}

	}

?>