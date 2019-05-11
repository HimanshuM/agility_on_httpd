<?php

namespace Agility\Http\Sessions;

use Agility\Chrono\Chronometer;
use Agility\Config;
use Agility\Http\Security\Secure;

	class CookieStore {

		protected $encryption;

		function __construct() {
			$this->encryption = "aes-128-gcm";
		}

		protected function decode($encoded) {
			return explode("#", base64_decode($encoded));
		}

		protected function encode($data, $iv, $tag) {
			return base64_encode($iv."#".$data."#".$tag);
		}

		/*protected function iv() {
			return openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encryption));
		}*/

		function readSession($cookie) {

			list($iv, $rawEncryptedSession, $tag) = $this->decode($cookie);

			// $content = openssl_decrypt($rawEncryptedSession, $this->encryption, Config::security()->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
			$content = Secure::decrypt($rawEncryptedSession, $this->encryption, Config::security()->encryptionKey, $iv, $tag);
			if ($serializedSession === false) {
				return false;
			}

			list($ctime, $serializedSession) = explode(PHP_EOL, $content);
			$session = unserialize($serializedSession);

			$createdAt = Chronometer::fromTimestamp(intval($ctime));

			if (Session::invalid($createdAt)) {
				return [false, false];
			}

			return [$session, $createdAt];

		}

		function writeSession($session) {

			$serializedSession = serialize($session);
			$content = $session->createdAt->timestamp.PHP_EOL.$serializedSession;

			// $iv = $this->iv();
			// $tag = null;

			// $rawEncryptedSession = openssl_encrypt($content, $this->encryption, Config::security()->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
			list($rawEncryptedSession, $iv, $tag) = Secure::encrypt($content, $this->encryption, Config::security()->encryptionKey);

			$session->cookie->value = $this->encode($rawEncryptedSession, $iv, $tag);

			return $session->cookie;

		}

	}

?>