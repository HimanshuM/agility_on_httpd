<?php

namespace Agility;

use ArrayUtils\Arrays;

	class Cli {

		private $_argv;

		function __construct($argv) {

			$this->_argv = new Arrays($argv);
			$this->loadApp();

		}

		function help() {
			echo "Help command is not implemented yet.";
		}

		function loadApp() {

			if (!$this->_argv->second) {
				return Commands::invoke($this->_argv->slice(1));
			}

			if (!AppLoader::executeApp($this->_argv->slice(1))) {
				return Commands::invoke($this->_argv->slice(1));
			}

		}

	}

?>