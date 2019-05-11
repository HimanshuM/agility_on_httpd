<?php

namespace Agility\Console\Commands;

use Agility\Http\Security\Secure;

	class SecretCommand extends Base {

		function generateKey() {
			return Secure::randomBytes("aes-128-gcm");
		}

		function generateKeys() {

			$keys = [];
			foreach (["development", "test", "production"] as $env) {
				$keys[$env] = ["encryption_key" => base64_encode($this->generateKey())];
			}

			return $keys;

		}

		function perform($args) {

			if (!$this->requireApp()) {
				return;
			}

			// $this->initializeApplication($args);
			if (!Secure::appHasSecurityFile($this->_appRoot)) {

				$securityJson = $this->_appRoot->touch("config/security.json");
				$securityJson->write(json_encode($this->generateKeys(), JSON_PRETTY_PRINT));

				echo "Security keys have been generated and written to config/security.json\n";

			}
			else {
				echo "config/security.json is already present. Please delete the file to regenerate.\n";
			}

		}

	}

?>