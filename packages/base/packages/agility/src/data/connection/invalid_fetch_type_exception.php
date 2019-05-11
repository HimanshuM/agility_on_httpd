<?php

namespace Agility\Data\Connection;

use Exception;

	class InvalidFetchTypeException extends Exception {

		function __construct($type) {
			parent::__construct("Invalid fetch type '$type' specified");
		}

	}

?>