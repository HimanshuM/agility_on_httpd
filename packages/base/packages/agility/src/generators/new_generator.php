<?php

namespace Agility\Generators;

use FileSystem\File;
use StringHelpers\Str;

	class NewGenerator extends Base {

		public $apiOnly;
		public $appPublicName;
		protected $composer = true;

		protected function __construct($appPath, $root, $args) {

			parent::__construct($appPath, $root, $args, "new");
			$this->_appName = $this->_args->shift;
			$this->appPublicName = Str::humanize($this->_appName);
			$this->_parseOptions(["api-only", "composer"]);

		}

		function appName() {
			echo $this->_appName;
		}

		protected function _generate() {

			parent::_generate();

			if (($composer = $this->_installComposer()) < 1) {

				if ($composer == 0) {
					$this->echo("You have chosen to skip composer installation.\n");
				}

				$this->echo("Agility\\Mailer requires PhpMailer to be installed. Please install it manually from https://github.com/PHPMailer/PHPMailer to use the mailer feature.\n");

			}
			else {
				$this->_runComposerInstall();
			}

			$this->_initGit();

			$this->echo("All done! Happy coding :)\n");

		}

		protected function _initGit() {

			$this->echo("Initializing a new git repository...\n");
			passthru("cd ".$this->_appRoot." && git init");

		}

		protected function _installComposer() {

			if (!$this->composer) {
				return 0;
			}

			$return = 1;

			$this->echo("Installing composer locally...\n");

			$downloadPath = $this->_appRoot->touch("tmp/composer-setup.php");
			if (empty($downloadPath)) {

				$this->echo("#Red##B#Failed to download composer:#N# could not create temporary file in tmp/.\n");
				$this->echo("Please install manually from https://getcomposer.org\n");

				return -1;

			}

			$installPath = $this->_appRoot."/bin";

			$signature = trim(file_get_contents("https://composer.github.io/installer.sig"));
			$this->echo("Downloading composer setup...\n");
			copy("https://getcomposer.org/installer", $downloadPath->path);

			if (hash_file("SHA384", $downloadPath->path) != $signature) {

				$this->echo("#Red##B#Failed to download composer:#N# could not verify setup signature.\n");
				$this->echo("Please install manually from https://getcomposer.org\n");
				$return = -1;

			}
			else {

				passthru("php ".$downloadPath." --quite --install-dir=".$installPath." --filename=composer");
				$this->echo("Composer installed succesfully!\n");

			}

			$downloadPath->delete();

			return $return;

		}

		protected function _publish($template, $name, $data) {

			$name = $this->_sanitizeFileExtension($name);

			$templateName = "";
			if ($template->isFile()) {

				if ($this->apiOnly && strpos($template->path, "views")) {
					return;
				}

				$file = $this->_appRoot->touch($name);
				$file->write($data);

				if ($name == "bin/agility") {
					$file->chmod(0755);
				}

			}
			else {

				if ($this->apiOnly && strpos($template->cwd, "layout")) {
					return;
				}

				$this->_appRoot->mkdir($name);

			}

			$this->echo("\t#B#create  #N#$name\n");

		}

		private function _runComposerInstall() {

			$this->echo("Installing dependencies...\n");
			passthru("cd ".$this->_appRoot." && bin/composer install");

		}

		private function _sanitizeFileExtension($filename) {

			$extn = strpos($filename, ".at");
			if ($extn !== false && $extn == strlen($filename) - 3) {
				return substr($filename, 0, -3);
			}

			return $filename;

		}

	}

?>