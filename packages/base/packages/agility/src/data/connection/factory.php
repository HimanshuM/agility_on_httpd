<?php

namespace Agility\Data\Connection;

	class Factory {

		static function attemptConnection($connectionArray, $instanceType, $defaultPoolSize) {

			$adapter = $connectionArray["adapter"];

			if ($adapter == "mysql") {
				return new Mysql\MysqlConnector($connectionArray, $instanceType, $defaultPoolSize);
			}
			else {
				return null;
			}

		}

	}

?>