<?php

namespace Agility\Exceptions;

use Exception;

	class SerializationException extends Exception {

		function __construct($dir = 0, $msg = null) {

			if (empty($msg)) {

				if ($dir == 0) {
					$msg = "Failed to serialize object";
				}
				else {
					$msg = "Failed to unserialize object";
				}

			}

			parent::__construct($msg);

		}

	}

?>