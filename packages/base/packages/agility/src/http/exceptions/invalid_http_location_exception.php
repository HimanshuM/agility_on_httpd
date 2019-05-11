<?php

namespace Agility\Http\Exceptions;

use Exception;

	class InvalidHttpLocationException extends Exception {

		function __construct($location, $model = false) {
			parent::__construct("HTTP location can only be of type string".($model ? " or an Agility data model" : "").", ".gettype($location)." given");
		}

	}

?>