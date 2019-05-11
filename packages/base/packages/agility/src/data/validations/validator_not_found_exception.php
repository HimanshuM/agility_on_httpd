<?php

namespace Agility\Data\Validations;

use Exception;

	class ValidatorNotFoundException extends Exception {

		function __construct($validator) {
			parent::__construct("Validator with name '$validator' not found.");
		}

	}

?>