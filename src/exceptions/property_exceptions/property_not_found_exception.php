<?php

namespace Phpm\Exceptions\PropertyExceptions;

use Exception;

	class PropertyNotFoundException extends Exception {

		function __construct($attribute, $class) {
			parent::__construct("Property '$attribute' not found for class '$class'");
		}
	}

?>