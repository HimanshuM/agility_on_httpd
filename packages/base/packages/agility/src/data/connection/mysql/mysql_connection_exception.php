<?php

namespace Agility\Data\Connection\Mysql;

use Exception;

	class MysqlConnectionException extends Exception {

		function __construct($message) {
			parent::__construct($message);
		}

	}

?>