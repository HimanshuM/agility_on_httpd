<?php

namespace Agility\Mailer;

use AttributeHelper\Accessor;
use Phpm\Exceptions\PropertyExceptions\PropertyNotFoundException;

	class Smtp {

		use Accessor;

		protected $initialized = false;
		protected $host;
		protected $backupHost;
		protected $auth = true;
		protected $username;
		protected $password;
		protected $encryption = "tls";
		protected $port = 587;

		protected function __construct() {

			$this->readonly("initialized");
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "setProperty");

		}

		static function new($arr = []) {

			$config = new Smtp;
			$config->host = $arr["host"] ?? null;
			$config->backupHost = $arr["backupHost"] ?? null;
			$config->auth = $arr["auth"] ?? true;
			$config->username = $arr["username"] ?? null;
			$config->password = $arr["password"] ?? null;
			$config->encryption = $arr["encryption"] ?? "tls";
			$config->port = $arr["port"] ?? 587;
			$config->validateInitialization();

			return $config;

		}

		protected function setProperty($name, $value = null) {

			if (!in_array($name, ["host", "backupHost", "auth", "username", "password", "encryption", "port"])) {
				throw new PropertyNotFoundException($name, Smtp::class);
			}

			if (is_null($value)) {
				return $this->$name;
			}

			$this->$name = $value;

			$this->validateInitialization();

		}

		protected function validateInitialization() {

			if (!empty($this->host) && ($this->auth === false || (!empty($this->username) && !empty($this->password) && !empty($this->encryption)))) {
				$this->initialized = true;
			}

		}

	}

?>