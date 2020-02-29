<?php

namespace Agility\Tasks;

use Agility\Console\Command;
use Agility\Console\Commands\ServerCommand;
use Agility\Console\Helpers\ArgumentsHelper;
use Agility\Console\Helpers\EchoHelper;

	class Base {

		use EchoHelper;

		protected $quite;

		function __construct() {

			$server = new ServerCommand;
			$server->perform(["--task-only"]);

		}

		protected function ask($prompt) {

			echo $prompt." ";
			return readline();

		}

		protected function parseOptions($args) {
			ArgumentsHelper::parseOptions($args, true);
		}

		protected function runTask($taskName, $args = []) {
			Command::invoke($taskName, $args);
		}

	}

?>