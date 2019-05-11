<?php

namespace Agility\Console\Commands;

use Agility\Generators\NewGenerator;
use Agility\Templating\Template;
use FileSystem\FileSystem;
use StringHelpers\Str;

	class NewCommand extends Base {

		private function _checkOrCreateDirectory($appPath) {

			if (FileSystem::cwd()->has($appPath)) {

				$appPath = FileSystem::cwd()->chdir($appPath);
				if (!$appPath->children()->empty) {
					return -1;
				}

			}
			else if (FileSystem::cwd()->mkdir($appPath) === false) {
				return 0;
			}
			else {
				$appPath = FileSystem::cwd()->chdir($appPath);
			}

			$this->_appPath = $appPath;
			return 1;

		}

		function perform($args) {

			if (!empty($this->_appPath)) {

				echo "Cannot initialize an Agility application inside another Agility application. Please choose a different location.";
				return;

			}

			if (($appPath = $args->shift()) == null) {

				echo "Agility application path not specified. Please use 'agility new <app_path>' to create a new Agility application.";
				return;

			}

			$appPath = Str::snakeCase($appPath);

			$status = $this->_checkOrCreateDirectory($appPath);
			if ($status == 0) {

				echo "Unable to create directory '$appPath'. Please check write permissions to directory and try again.";
				return;

			}
			else if ($status == -1) {

				echo "Cannot initialize an Agility application inside '$appPath', the directory is not empty. Please choose a different location.";
				return;

			}

			$this->getAppName();
			NewGenerator::start($this->_appPath, $this->_appRoot, $args->prepend($this->_appName));


		}

		private function _populateAppDir() {

			// $generator = new GeneratorBase("new", $this);
			// $generator->addMethods("appName");

			// Cli::echo("\t#B#WCreate...\n");
			// $generator->generate("publish");

			// echo "All done! Happy coding :)\n";

		}

	}

?>