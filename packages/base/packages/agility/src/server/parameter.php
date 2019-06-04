<?php

namespace Agility\Server;

use ArrayUtils\Arrays;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	class Parameter extends Arrays {

		function require($keys) {
			return new Parameter($this->fetch($keys, new Exceptions\ParameterMissingException($keys)));
		}

		function permit($keys = []) {

			if (empty($keys)) {
				return $this;
			}

			return new Parameter($this->pick($keys));

		}

	}

?>