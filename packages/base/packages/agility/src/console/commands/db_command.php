<?php

namespace Agility\Console\Commands;

use Agility\Data\Connection\Pool;
use Agility\Data\Migration\Runner;
use Agility\Initializers\ApplicationInitializer;
use ArrayUtils\Arrays;
use StringHelpers\Str;

	class DbCommand extends Base {

		use ApplicationInitializer;

		protected $migrationRunner;

		function __construct() {

			parent::__construct();
			$this->migrationRunner = new Runner($this->appPath->parent->chdir("../db/migrate"));
			// putenv("AGILITY_NO_ECHO=1");

		}

		function drop($args) {

			if (!$this->requireApp()) {
				return;
			}

			$this->initializeApplication($args, true);
			echo "Resetting database...\n";
			foreach (Pool::$pool as $connection) {
				$connection->resetDatabase();
			}
			echo "Database reset.\n";

		}

		function migrate($args) {

			if (!$this->requireApp()) {
				return;
			}

			$this->initializeApplication($args, true);
			$count = $this->migrationRunner->executePendingMigrations();
			if ($count == 0) {
				echo "Nothing to migrate.";
			}
			else {
				echo "$count migration".($count > 1 ? "s" : "")." processed.";
			}

		}

		function reset($args) {

			if (!$this->requireApp()) {
				return;
			}

			// $this->initializeApplication($args);

			$this->drop($args);
			$this->migrate($args);

		}

		function seed($args) {

			if (!$this->requireApp()) {
				return;
			}

			$this->initializeApplication($args, true);

		}

	}

?>