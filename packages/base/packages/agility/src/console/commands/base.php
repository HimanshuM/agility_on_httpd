<?php

namespace Agility\Console\Commands;

use Agility\Console\Helpers\EchoHelper;
use AttributeHelper\Accessor;
use FileSystem;
use MethodTriggers\Trigger;
use StringHelpers\Str;

	abstract class Base {

		use Accessor;
		use Trigger;

		protected $_appName = "";
		protected $_appPath = null;
		protected $_appRoot = null;
		protected $_basePath;
		protected $_environment;

		protected $_args;

		function __construct() {

			$this->_basePath = FileSystem\FileSystem::path(__DIR__);

			if (defined("APP_PATH")) {
				$this->_appPath = FileSystem\FileSystem::path(constant("APP_PATH"));
			}

			$this->getAppName();
			$this->getAgilityEnvironment();

			$this->prependUnderscore();
			$this->readonly("basePath", "appPath", "appName", "environment");

		}

		protected function getAgilityEnvironment() {

			if (($env = getenv("AGILITY_ENV")) === false) {
				$env = "development";
			}

			$this->_environment = $env;

		}

		protected function getAppName() {

			if (!empty($this->_appPath)) {

				if ($this->_appPath->isDir()) {

					$this->_appRoot = $this->_appPath;
					$this->_appName = Str::camelCase($this->_appPath->basename);

				}
				else {

					$this->_appRoot = $this->_appPath->parent;
					$appName = $this->_appRoot->chdir("..")->basename;
					$this->_appName = Str::camelCase($appName);

				}

			}

		}

		protected function requireApp() {

			if (empty($this->_appPath)) {

				echo "Unable to locate Agility application. Please create an Agility application first, using 'agility new <app_name>.";
				return false;

			}

			return true;

		}

	}

?>