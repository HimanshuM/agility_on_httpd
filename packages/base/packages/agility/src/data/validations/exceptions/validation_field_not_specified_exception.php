<?php

namespace Agility\Data\Validations\Exceptions;

use Exception;

	class ValidationFieldNotSpecifiedException extends Exception {

		function __construct() {
			parent::__construct("'fields' key must be present in Model::validatesWith(\$name, \$args = []);");
		}

	}

?>