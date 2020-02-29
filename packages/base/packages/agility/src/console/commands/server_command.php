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
			$app = new $className;
			if (in_array("--init-only", $args)) {
				$app->initialize();
			}
			else {
				$app->run();
			}

		}

	}

?>