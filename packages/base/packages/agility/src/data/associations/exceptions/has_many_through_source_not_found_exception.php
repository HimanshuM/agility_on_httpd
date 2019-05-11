<?php

namespace Agility\Data\Associations\Exceptions;

use Exception;

	class HasManyThroughSourceNotFoundException extends Exception {

		function __construct($through, $source, $owner) {
			parent::__construct("Belongs to association of '$through' to '$source' not found for '$owner'");
		}

	}

?>