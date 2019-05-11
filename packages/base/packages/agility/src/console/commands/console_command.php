<?php

namespace Agility\Console\Commands;

	class ConsoleCommand extends Base {

		function perform($args) {

			if (!$this->requireApp()) {
				return;
			}

			$this->initializeApplication($args);

		}

	}

?>