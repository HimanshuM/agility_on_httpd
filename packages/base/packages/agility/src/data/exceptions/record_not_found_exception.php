<?php

namespace Agility\Data\Exceptions;

use Exception;

	class RecordNotFoundException extends Exception {

		function __construct($column, $value) {

			$msg = "Record with $column $value not found";
			if (is_array($value)) {
				$msg = "Records with $column (".implode(", ", $value).") not found";
			}

			parent::__construct($msg);

		}

	}

?>