<?php

namespace Agility\Server;

use ArrayUtils\Arrays;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	class Parameter extends Arrays {

		function require($keys) {

			$filter = $this->fetch($keys, new Exceptions\ParameterMissingException($keys));
			if (is_array($keys) || is_a($keys, Arrays::class)) {

				if (!$filter->ignore($keys)->empty) {
					throw new Exceptions\ParameterMissingException($keys);
				}

			}

			return $filter;

		}

		function permit($keys = []) {

			if (empty($keys)) {
				return $this;
			}

			return $this->pick($keys);

		}

	}

?>