<?php

namespace Agility\Server\Exceptions;

use Agility\Http\Exceptions\HttpException;

	class ParameterMissingException extends HttpException {

		function __construct($keys) {

			$this->httpStatus = 400;

			if (is_array($keys)) {
				parent::__construct("One or more of ".Inflect::toSentence($expected, ",")." keys not found in params array");
			}
			else {
				parent::__construct("Key ".$keys." not found in params array");
			}

		}

	}

?>