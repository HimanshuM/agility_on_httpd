<?php

namespace Agility\Data\Validations\Exceptions;

use Exception;

	class MissingValidationAttributeException extends Exception {

		function __construct($validator, $attribute) {
			parent::__construct("$validator expects '$attribute' to be present in the options");
		}

	}

?>