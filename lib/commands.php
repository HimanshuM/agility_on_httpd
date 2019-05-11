<?php

namespace Phpm;

	final class Commands {

		private $_home;
		private $_argv;
		private $_packageSet;
		private $_packageSetDir;

		function __construct($argv) {

			$this->_home = realpath(__DIR__."/../")."/";

			if (($packageSetDir = getenv("PHPMP_HOME")) === false) {
				$packageSetDir = realpath(__DIR__."/../packages/default/");
			}
			$this->_packageSetDir = $packageSetDir;

			$packageSetDir = strrev(trim($packageSetDir, "/"));
			$this->_packageSet = explode("/", $packageSetDir)[0];

			if (count($argv) > 1) {
				$this->_argv = array_slice($argv, 1);
			}
			else {
				$this->_argv[] = "help";
			}
			$this->parseCommand();

			echo "\n";

		}

		private function parseCommand() {

			if (in_array($this->_argv[0], ["help", "install", "packageset", "test", "use"])) {

				$command = $this->_argv[0];
				$this->$command();

			}
			else {
				echo "Invalid command '$command'. Please enter 'help' to see the list of available commands.";
			}

		}

		private function help($command = null) {

			if (empty($command)) {
				echo "This command is not yet implemented. Please try again later.";
			}
			else if ($command == "use") {
				echo "No packageset mentioned.";
			}
			else if ($command == "test") {

			}

		}

		private function install() {
			echo "This command is not yet implemented. Please try again later.";
		}

		private function packageset() {
			echo "This command is not yet implemented. Please try again later.";
		}

		private function test() {

			if (empty($this->_argv[1])) {
				return $this->help("test");
			}

			$tests = array_slice($this->_argv, 1);
			$tester = new UnitTesting\Tester($tests);
			$tester->perform();

		}

		private function use() {

			if (empty($this->_argv[1])) {
				return $this->help("use");
			}

			$packageSet = $this->_argv[1];
			if (!$this->checkPackageSetExists($packageSet)) {

				echo "No package set exists by name '$packageSet'. Please create one using 'phpm packageset create <packageset_name>' command.";
				return;

			}

			$default = false;
			if (!empty($this->_argv[2]) && $this->_argv[2] == "default") {
				$default = true;
			}

			$this->loadPackageSet($packageSet, $default);
			echo "Using packageset '$packageSet'.";

		}

		private function checkPackageSetExists($packageSet) {

			if (!file_exists($this->_home."packages/$packageSet") || !file_exists($this->_home."packages/$packageSet/environment")) {
				return false;
			}
			return true;

		}

		private function loadPackageSet($packageSet, $default) {

			if ($default) {

				unlink($this->_home."packages/default");
				exec("ln -s ".$this->_home."packages/$packageSet default");

			}
			else {
				// exec("source ");
			}

		}

	}

?>