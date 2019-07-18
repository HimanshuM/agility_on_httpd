<?php

namespace Agility\Initializers;

use Agility\Configuration;

	final class PreInitializer {

		static function execute() {

			$configDir = Configuration::documentRoot()->chdir("config");
			if (($envFile = $configDir->has(Configuration::environment().".php"))) {
				require_once $envFile;
			}

		}

	}

?>