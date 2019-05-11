<?php

namespace Agility\Http\Sessions;

use Agility\Chrono\Chronometer;
use Agility\Config;
use Agility\Data\Connection\Mysql\MysqlConnector;

	class DatabaseStore extends BackendStore {

		protected $model;

		function __construct($model) {
			$this->model = $model;
		}

		function cleanup () {

			$sessionClass = $this->model;
			$query;
			if (is_a($sessionClass::connection(), MysqlConnector::class)) {
				$query = "DELETE FROM ".$sessionClass::tableName()." WHERE TIMESTAMPDIFF(SECOND, created_at, CUREENT_TIMESTAMP) > ?";
			}
			$oldSessions = $sessionClass::exec($query, Config::sessionStore()->expiry);

		}

		function deleteSession($session) {

			$className = $this->model;
			$className::execute("DELETE FROM ".$className::tableName()." WHERE ".$className::$primaryKey." = ?;", $session->id);

		}

		function readSession($sessionId) {

			$className = $this->model;
			$sessionObject = $className::find($sessionId);
			if ($sessionObject == false) {
				return [false, false];
			}

			$serializedSession = $sessionObject->data;
			$session = unserialize($serializedSession);

			if (Session::invalid($sessionObject->createdAt)) {

				$sessionObject->delete();
				return [false, false];

			}

			return [$session, $sessionObject->createdAt];

		}

		function writeSession($session) {

			$className = $this->model;
			if ($session->fresh) {

				$className::create(function($s) use ($session) {

					$s->sessionId = $session->id;
					$s->data = serialize($session);
					$s->createdAt = $session->createdAt;

				});

			}
			else {

				$className = $this->model;
				$className::execute("UPDATE ".$className::tableName()." SET data = ? WHERE ".$className::$primaryKey." = ?;", serialize($session), $session->id);

			}

		}

	}

?>