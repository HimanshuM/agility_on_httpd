<?php

namespace Agility\Data;

use Exception;

	class InvalidSqlOperationException extends Exception {

		function __construct() {
			parent::__construct("Invalid Sql operation specified");
		}

	}

?>