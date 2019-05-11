<?php

namespace Phpm\Exceptions\ClassExceptions;

use Exception;

	class ClassNotFoundException extends Exception {

		function __construct($class, $method = false) {

			$msg = "Class '$class' not found";
			if (!empty($method)) {
				$msg .= " in method ".$method;
			}

			parent::__construct($msg);

		}

	}

?>