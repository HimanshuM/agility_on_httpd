<?php

namespace Agility\Console\Commands;

use Agility\Initializers\ApplicationInitializer;

	class AssetCommand extends Base {

		use ApplicationInitializer;

		function compile($args) {

			if (!$this->requireApp()) {
				return;
			}

		}

	}

?>