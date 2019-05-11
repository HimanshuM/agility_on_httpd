<?php

namespace Agility\Caching;

use Exception;

	class InvalidOperationException extends Exception {

		function __construct($operation, $object, $because = "") {
			parent::__construct("Invalid operation '$operation' on $object.".(!empty($because) ? "It is not a".(in_array($because[0], ["a", "e", "i", "o", "u"]) ? "n" : "")." $because." : ""));
		}

	}

?>