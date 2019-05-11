<?php

namespace Agility\Data\Associations;

use Exception;

	class InvalidThroughExpressionException extends Exception {

		function __construct($through) {
			parent::__construct("Through expression '$through' not found");
		}

	}

?>