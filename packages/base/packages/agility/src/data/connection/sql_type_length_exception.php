<?php

namespace Agility\Data\Connection;

use Exception;

	class SqlTypeLengthException extends Exception {

		function __construct($type, $length) {
			parent::__construct("No $type type has size $length");
		}

	}

?>