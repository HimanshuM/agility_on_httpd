<?php

namespace Agility\Routing\Exceptions;

use Exception;

	class CallbackNotSpecifiedException extends Exception {

		function __construct($method) {
			parent::__construct("Callback not specified for method '$method'");
		}

	}

?>