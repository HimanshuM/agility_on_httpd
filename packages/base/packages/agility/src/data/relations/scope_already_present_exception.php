<?php

namespace Agility\Data\Relations;

use Exception;

	class ScopeAlreadyPresentException extends Exception {

		function __construct($name, $owner) {
			parent::__construct("Class $owner already has a scope with name '$name'");
		}

	}

?>