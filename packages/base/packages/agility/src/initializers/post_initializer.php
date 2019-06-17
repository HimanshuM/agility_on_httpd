<?php

namespace Agility\Initializers;

use Agility\Configuration;
use StringHelpers\Str;

	final class PostInitializer {

		static $initializers = [
			"data_initializer",
		];

		static function execute() {

			// foreach (PostInitializer::$initializers as $initializer) {

			// 	if (Configuration::documentRoot()->has("/config/initializers/$initializer.php")) {

			// 		require_once(Configuration::documentRoot()->has("/config/initializers/$initializer.php"));
			// 		$className = Str::camelCase($initializer);
			// 		if (class_exists($className)) {
			// 			(new $className)->configure();
			// 		}

			// 	}

			// }

			foreach (Configuration::documentRoot()->children("config/initializers/") as $initializer) {
				require_once $initializer;
			}

			static::executeEnvironmentInitializers();

		}

		static function executeEnvironmentInitializers() {

			$envPath = "config/".Configuration::environment();
			if (Configuration::documentRoot()->has($envPath)) {

				foreach (Configuration::documentRoot()->children($envPath) as $initializer) {
					require_once $initializer;
				}

			}

		}

	}

?>