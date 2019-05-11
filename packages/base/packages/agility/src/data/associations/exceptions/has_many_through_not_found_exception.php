<?php

namespace Agility\Data\Associations\Exceptions;

use Exception;

	class HasManyThroughNotFoundException extends Exception {

		function __construct($through, $class) {
			parent::__construct("Association '$through' not found in class $class");
		}

	}

?>