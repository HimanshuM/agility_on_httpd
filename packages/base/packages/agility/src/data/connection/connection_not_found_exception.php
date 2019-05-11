<?php

namespace Agility\Data\Connection;

use Exception;

	class ConnectionNotFoundException extends Exception {

		function __construct($connectionName = false) {
			parent::__construct("Invalid connection name specified".($connectionName !== false ? " '".$connectionName."'" : ""));
		}

	}

?>