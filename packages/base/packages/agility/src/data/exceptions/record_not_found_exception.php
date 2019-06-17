<?php

namespace Agility\Data\Exceptions;

use Exception;

	class RecordNotFoundException extends Exception {

		function __construct($model = "", $column = "", $value = "") {

			$msg = "Record not found";
			if (!empty($model)) {

				$model = str_replace("App\\Models\\", "", $model);
				$msg = "Could not find $model with '$column' = $value";
				if (is_array($value)) {
					$msg = "Could not find $model with '$column' in (".implode(", ", $value).")";
				}

			}

			parent::__construct($msg);

		}

	}

?>