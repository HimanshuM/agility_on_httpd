<?php

namespace Agility\Data\Types;

use Agility\Exceptions\ClassNotFoundException;

	class SqlTypeNotFoundException extends ClassNotFoundException {

		function __construct($typeName) {
			parent::__construct("Sql type by name '$typeName' not found", true);
		}

	}

?>