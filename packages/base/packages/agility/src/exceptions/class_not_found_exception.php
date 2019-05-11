<?php

namespace Agility\Exceptions;

use Exception;

	class ClassNotFoundException extends Exception {

		function __construct($className, $overwrite = false) {

			if (!$overwrite) {
				$className = "Class '$className' not found";
			}

			parent::__construct($className);

		}

	}

?>