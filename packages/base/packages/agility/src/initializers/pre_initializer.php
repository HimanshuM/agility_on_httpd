<?php

namespace Agility\Initializers;

use Agility\Configuration;

	final class PreInitializer {

		static function execute() {

			$configDir = Configuration::documentRoot()->chdir("config");
			if (($envFile = $configDir->has("environment.php"))) {
				require_once $envFile;
			}

			if ($configDir->has("environments")) {

				$envDir = $configDir->chdir("environments");
				if (($envFile = $envDir->has(Configuration::environment().".php"))) {
					require_once $envFile;
				}

			}

		}

	}

?>