<?php

namespace Agility\Console\Commands;

use Agility\Initializers\ApplicationInitializer;

	class ServerCommand extends Base {

		use ApplicationInitializer;

		function perform($args) {

			if (!$this->requireApp()) {
				return;
			}

			$className = $this->loadApplication($args);

			if (/*$this->_environment == "production"*/false) {

				$this->exportOptions();

			}
			else {

				$app = new $className;
				$app->run(!in_array("--task-only", $args));

			}

		}

	}

?>