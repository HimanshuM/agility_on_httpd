<?php

namespace Agility\Http\Sessions;

use Agility\Chrono\Chronometer;
use Agility\Config;
use Agility\Http\Cookie;
use ArrayUtils\Arrays;

	class Session extends Arrays {

		protected $id;
		protected $cookie;
		// Unset when the session was created from cookie or header
		protected $fresh = true;
		protected $createdAt;

		function __construct() {

			parent::__construct();

			if (!Config::sessionStore()->cookieStore) {
				$this->initializeId();
			}

			$this->createdAt = new Chronometer;

			$this->cookie = new Cookie(Config::sessionStore()->cookieName);

			$this->methodsAsProperties("invalidate");
			$this->readonly("id", "cookie", "createdAt", "fresh");

		}

		static function buildFromCookie($cookie) {

			if (Config::sessionStore()->cookieStore) {

				if (($session = Config::sessionStore()->cookieStore->readSession($cookie)) === false) {
					return new Session;
				}

				$session->fresh = false;
				return $session;

			}
			else {
				return Session::buildFromBackend($cookie);
			}

		}

		static function buildFromHeader($header) {

			$sessionId = false;
			if (Config::sessionStore()->sessionSource["header"] == "authorization") {

				$authorization = Config::sessionStore()->sessionSource["authorization"] ?? "bearer";
				$authorization = ucfirst($authorization);
				$sessionId = str_replace($authorization, "", $header);

			}
			else {
				$sessionId = $header;
			}

			return Session::buildFromBackend(trim($sessionId));

		}

		static function buildFromBackend($sessionId) {

			$session = false;
			if (Config::sessionStore()->fileStore) {
				list($session, $createdAt) = Config::sessionStore()->fileStore->readSession($sessionId);
			}
			else {
				list($session, $createdAt) = Config::sessionStore()->databaseStore->readSession($sessionId);
			}

			if ($session !== false) {

				$session->id = $sessionId;
				if (Config::sessionStore()->expiryScheme == Configuration::ConstantExpiry) {
					$session->createdAt = $createdAt;
				}
				else {
					$session->createdAt = new Chronometer;
				}

				$session->fresh = false;

			}
			else {
				$session = new Session;
			}

			return $session;

		}

		protected function deleteFile() {
			Config::sessionStore()->fileStore->deleteSession($this);
		}

		protected function deleteStorage() {

			if (Config::sessionStore()->fileStore) {
				$this->deleteFile();
			}
			else if (Config::sessionStore()->databaseStore) {
				$this->deleteFromDb();
			}

		}

		protected function initializeId() {
			$this->id = hash("sha256", microtime());
		}

		function initializeFromId($sessionId) {

			$session = static::buildFromBackend($sessionId);

			$this->id = $session->id;
			$this->cookie = $session->cookie;
			$this->fresh = $session->fresh;
			$this->createdAt = $session->createdAt;

			$this->copyFrom($session);

		}

		static function invalid($createdAt) {
			return time() - $createdAt->timestamp > Config::sessionStore()->expiry;
		}

		function invalidate() {

			$this->clear();
			$this->deleteStorage();
			$this->fresh = true;

		}

		function persist() {

			if (Config::sessionStore()->fileStore) {
				$this->persistToFile();
			}
			else if (Config::sessionStore()->databaseStore) {
				$this->persistToDb();
			}

			return $this->write();

		}

		protected function persistToCookie() {

		}

		protected function persistToDb() {
			Config::sessionStore()->databaseStore->writeSession($this);
		}

		protected function persistToFile() {
			Config::sessionStore()->fileStore->writeSession($this);
		}

		function serialized() {
			return serialize($this);
		}

		protected function write() {

			if (Config::sessionStore()->cookieStore) {
				Config::sessionStore()->cookieStore->writeSession($this);
			}
			else {

				if (!$this->fresh || Config::sessionStore()->sessionSource != "cookie") {
					return;
				}

				$this->cookie->value = $this->id;
				if (!empty(Config::sessionStore()->secureCookie)) {
					$this->cookie->httponly = true;
				}
				// $this->cookie->write($response);

			}

			return $this->cookie;

		}

	}

?>