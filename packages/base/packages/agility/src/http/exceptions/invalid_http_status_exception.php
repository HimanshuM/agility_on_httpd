<?php

namespace Agility\Http\Exceptions;

	class InvalidHttpStatusException extends HttpException {

		function __construct($status) {
			parent::__construct("HTTP status can only be of type integer, ".gettype($status)." given");
		}

	}

?>