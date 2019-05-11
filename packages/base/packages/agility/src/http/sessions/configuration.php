<?php

namespace Agility\Http\Sessions;

use Agility\Config;
use AttributeHelper\Accessor;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;

	class Configuration {

		use Accessor;

		const ConstantExpiry = 0;
		const IncrementalExpiry = 1;

		public $cookieName = "agility_sess";
		public $secureCookie = false;
		protected $storage = "FileStore";
		public $sessionSource = "cookie";
		protected $expiry = 1200;
		protected $expiryScheme = 1;

		protected $cookieStore = false;
		protected $databaseStore = false;
		protected $fileStore = false;

		function __construct() {

			$this->cookieStore = new CookieStore;
			$this->fileStore = new FileStore;

			$this->methodsAsProperties();

		}

		function cookieStore() {
			return $this->storage == "CookieStore" ? $this->cookieStore : false;
		}

		function databaseStore() {
			return $this->databaseStore;
		}

		function expiry($value = nil) {

			if ($value === nil) {
				return $this->expiry;
			}

			if (!is_int($value)) {
				throw new InvalidTypeException("Session store expiry", "integer");
			}

			$this->expiry = $value;

		}

		function expiryScheme($value = nil) {

			if ($value === nil) {
				return $this->expiryScheme;
			}

			if (!is_int($value)) {
				throw new InvalidTypeException("Session store expiry scheme", "integer");
			}
			if ($value != 0 && $value != 1) {
				throw new Exception("Session store expiry scheme can only have 2 values, Configuration::ConstantExpiry or Configuration::IncrementalExpiry");
			}

			$this->expiryScheme = $value;

		}

		function fileStore() {
			return $this->storage == "FileStore" ? $this->fileStore : false;
		}

		function storage($value = nil) {

			if ($value === nil) {

				if ($this->storage == "CookieStore") {
					return $this->cookieStore;
				}
				else if ($this->storage == "FileStore") {
					return $this->fileStore;
				}

				return $this->databaseStore;

			}

			if ($value != "CookieStore" && $value != "FileStore") {
				$this->databaseStore = new DatabaseStore($value);
			}

			return $this->storage = $value;

		}

	}

?>