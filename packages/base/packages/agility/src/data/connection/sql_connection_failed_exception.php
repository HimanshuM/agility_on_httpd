<?php

namespace Agility\Data\Connection;

use Exception;

	class SqlConnectionFailedException extends Exception {

		function __construct($message) {
			parent::__construct($message);
		}

	}

?>