<?php

namespace Phpm\Exceptions\DataExceptions;

use Exception;

	class InvalidJsonException extends Exception {

		function __construct($content, $file = false) {

			$msg = "Invalid JSON found";
			if ($file !== false) {
				$msg .= " in file ".$content;
			}

			parent::__construct($msg);

		}

	}

?>