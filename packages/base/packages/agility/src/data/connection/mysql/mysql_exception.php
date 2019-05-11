<?php

namespace Agility\Data\Connection\Mysql;

	class MysqlException {

		const SqlStates = [
			"1049" => "NoDatabaseError",
			"42S02" => "TableNotFoundError",
		];

		static function parseException($e) {

			$message = $e->getMessage();
			if (!empty(MysqlException::SqlStates[$e->getCode()])) {

				$exceptionClass = MysqlException::SqlStates[$e->getCode()];
				$message = MysqlException::parseMessage($e->getMessage());

			}
			else {
				$exceptionClass = "SqlException";
			}

			$exceptionClass = "\\Agility\\Data\\Exceptions\\".$exceptionClass;
			return new $exceptionClass($message);

		}

		static function parseMessage($message) {

			$matches;
			if (preg_match('/SQLSTATE\[.*\]:?(.+:\s+\d+)?(.+)/', $message, $matches)) {
				return trim($matches[count($matches) - 1]);
			}

			return $message;

		}

	}

?>