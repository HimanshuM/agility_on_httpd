<?php

namespace Agility\Http\Security;

use Agility\Configuration;
use AttributeHelper\Accessor;

	class Secure {

		use Accessor;

		protected $encryptionKey = "";
		protected $forgeryProtection = false;

		function __construct($encryptionKey) {

			$this->encryptionKey = $encryptionKey;
			$this->readonly("encryptionKey", "forgeryProtection");

		}

		static function appHasSecurityFile($root) {
			return $root->has("config/security.json");
		}

		static function decrypt($encryptedData, $encryptionMethod, $encryptionKey, $randomBytes, $tag) {
			return openssl_decrypt($encryptedData, $encryptionMethod, $encryptionKey, OPENSSL_RAW_DATA, $randomBytes, $tag);
		}

		static function encrypt($data, $encryptionMethod, $encryptionKey) {

			$randomBytes = Secure::randomBytes($encryptionMethod);
			$tag = null;

			$encryptedData = openssl_encrypt($data, $encryptionMethod, $encryptionKey, OPENSSL_RAW_DATA, $randomBytes, $tag);

			return [$encryptedData, $randomBytes, $tag];

		}

		static function initialize() {

			Configuration::security(new Secure(""));

			if (($securityFile = Secure::appHasSecurityFile(Configuration::documentRoot())) !== false) {
				Secure::parseSecurityJson($securityFile);
			}

		}

		static function randomBytes($encryptionOrLength) {

			if (is_string($encryptionOrLength)) {
				$encryptionOrLength = openssl_cipher_iv_length($encryptionOrLength);
			}
			else {
				$encryptionOrLength = intval($encryptionOrLength);
			}

			return openssl_random_pseudo_bytes($encryptionOrLength);

		}

		static function parseSecurityJson($securityJson) {

			$securityJson = json_decode(file_get_contents($securityJson), true);
			$securityJson = $securityJson[Configuration::environment()];

			Configuration::security()->encryptionKey = base64_decode($securityJson["encryption_key"]);

		}

		function protectFromForgery() {
			$this->forgeryProtection = true;
		}

		static function secureDecode($encodedData, $encryptionMethod, $encryptionKey) {

			list($randomBytes, $encryptedData, $tag) = explode("#", base64_decode($encodedData));
			return Secure::decrypt($encryptedData, $encryptionMethod, $encryptionKey, $randomBytes, $tag);

		}

		static function secureEncode($data, $encryptionMethod, $encryptionKey) {

			list($encryptedData, $randomBytes, $tag) = Secure::encrypt($data, $encryptionMethod, $encryptionKey);
			return base64_encode($randomBytes."#".$encryptedData."#".$tag);

		}

	}

?>