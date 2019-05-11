<?php

namespace Agility\Initializers;

use Agility\Application;
use Agility\Console\Helpers\ArgumentsHelper;
use Agility\Configuration;

	trait ApplicationInitializer {

		protected $application;

		function parseOptions($args) {

			$options = ArgumentsHelper::parseOptions($args);

			$environment = getenv("AGILITY_ENV") ?: $options["-e"] ?? $options["--environment"] ?? "development";
			$this->_environment = $environment;
			Configuration::initialize($this->_appRoot, $environment);

		}

		private function exportOptions() {
			putenv("AGILITY_ENV=".Configuration::environment());
		}

		private function initializeApplication($args, $runPostInitializers = false) {

			if (is_a($this->application, Application::class)) {
				return;
			}

			$this->instantiateApplication($args);
			$this->application->firstStageInitialization();
			if ($runPostInitializers) {
				$this->runPostInitializers();
			}

		}

		private function instantiateApplication($args) {

			$className = $this->loadApplication($args);
			return $this->application = new $className;

		}

		private function loadApplication($args) {

			$this->parseOptions($args);

			// var_dump($this->_appPath);

			require_once $this->_appPath->path;
			$className = $this->_appName."\\Application";
			if (!class_exists($className)) {

				echo "Could not locate class '$className'. Has the namespace or class name in config/application.php been edited?";
				return;

			}

			return $className;

		}

		private function runPostInitializers() {
			$this->application->executePostInitializers();
		}

	}

?>