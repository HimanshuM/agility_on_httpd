<?php

namespace Agility;

use ArrayUtils;

	class Commands {

		private $_argv;
		private $_appPath = null;

		const Aliases = [
			"c" => "console",
			"s" => "server",
			"g" => "generate",
			"v" => "version"
		];

		function __construct($argv) {

			if (is_a($argv, "ArrayUtils\\Arrays")) {
				$this->_argv = $argv;
			}
			else {
				$this->_argv = new ArrayUtils\Arrays(array_slice($argv, 1));
			}

			$this->parseCommand();

		}

		protected function parseCommand() {

			if ($this->_argv->empty) {
				$cmd = "help";
			}
			else {

				$cmd = $this->_argv->shift();
				$cmd = self::Aliases[$cmd] ?? $cmd;

			}

			Console\Command::invoke($cmd, $this->_argv);

		}

	}

?>