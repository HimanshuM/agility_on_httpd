<?php

namespace Aqua\Visitors\Exceptions;

use Exception;

	class EmptyInsertValuesException extends Exception {

		function __construct($table) {
			parent::__construct("Insert statement has neither values nor select for table $table");
		}

	}

?>