<?php

namespace Agility\Data\Exceptions;

	class AttributeDoesNotExistException extends SqlException {

		function __construct($name, $model) {
			parent::__construct("Attribute '$name' does not exist on model $model");
		}

	}

?>