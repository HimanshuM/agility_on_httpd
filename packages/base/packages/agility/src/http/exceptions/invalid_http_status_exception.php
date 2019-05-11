<?php

namespace Agility\Http\Exceptions;

use Exception;

	class InvalidHttpStatusException extends Exception {

		function __construct($status) {
			parent::__construct("HTTP status can only be of type integer, ".gettype($status)." given");
		}

	}

?>