<?php

namespace Agility\Data\Exceptions;

use Exception;

	class SqlException extends Exception {

		function __toString() {
			return str_replace("\\Exceptions", "", get_called_class()).": ".$this->message;
		}

	}

?>