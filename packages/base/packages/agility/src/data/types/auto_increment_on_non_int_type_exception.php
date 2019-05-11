<?php

namespace Agility\Data\Types;

use Exception;

	class AutoIncrementOnNonIntTypeException extends Exception {

		function __construct($dataType, $attrName) {
			parent::__construct("Invalid use of auto increment for column $attrName of type $dataType");
		}

	}

?>