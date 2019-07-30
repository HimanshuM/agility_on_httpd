<?php

namespace Agility\Data\Exceptions;

use Exception;
use StringHelpers\Inflect;

	class RecordNotFoundException extends Exception {

		function __construct($model = "", $column = "", $value = "") {

			$msg = "Record not found";
			if (!empty($model)) {

				$model = str_replace("App\\Models\\", "", $model);
				$msg = "Could not find $model with ";
				if (is_array($column)) {

					$attributes = [];
					foreach ($column as $key => $value) {
						$attributes[] = "'$key' = $value";
					}

					$msg .= Inflect::toSentence($attributes);

				}
				else {

					$msg .= "'$column' = $value";
					if (is_array($value)) {
						$msg = "Could not find $model with '$column' in (".implode(", ", $value).")";
					}

				}

			}

			parent::__construct($msg);

		}

	}

?>