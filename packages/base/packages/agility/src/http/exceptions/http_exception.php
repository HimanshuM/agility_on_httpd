<?php

namespace Agility\Http\Exceptions;

use Exception;

	abstract class HttpException extends Exception {
		public $httpStatus = 500;
	}

?>