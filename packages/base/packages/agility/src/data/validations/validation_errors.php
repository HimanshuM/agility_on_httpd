<?php

namespace Agility\Data\Validations;

use ArrayUtils\Arrays;

	class ValidationErrors extends Arrays {

		function add($name, $msg) {

			if (!$this->exists($name)) {
				$this[$name] = new Arrays;
			}

			$this[$name]->append($msg);

		}

		function allErrors() {
			return $this->invoke("array")->array;
		}

	}

?>