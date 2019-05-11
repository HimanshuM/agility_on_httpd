<?php

namespace Agility\Routing\Exceptions;

use Exception;

	class RoutesNotFoundException extends Exception {

		function __construct() {
			parent::__construct("Could not load routes. Please use config/routes.php to describe routes");
		}

	}

?>