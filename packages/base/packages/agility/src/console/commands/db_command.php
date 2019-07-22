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

			$seed = false;
			$pick = [];
			if ($args->count > 0) {

				foreach ($args as $arg) {

					if ($arg == "all") {

						$seed = true;
						break;

					}
					else if (in_array($arg, ["development", "test", "production"])) {

						$seed = true;
						$pick = $arg;

						break;

					}
					else {
						$pick[] = $arg;
					}

				}

			}
			else {
				$seed = true;
			}

			if ($seed) {
				$this->executeSeedFiles();
			}

			if (is_array($pick) && count($pick)) {

				foreach ($pick as $file) {

					if ($file == "seeds") {
						continue;
					}

					if ($seedsFile = $this->appPath->parent->chdir("../db/seeds")->has("$file.php")) {
						$this->executeFile($seedsFile);
					}

					$this->executeEnvironmentFiles($file);

				}

			}
			else {

				if (empty($pick)) {

					$seeds = $this->appPath->parent->children("../db/seeds");
					foreach ($seeds as $seed) {
						$this->executeFile($seed);
					}

				}

				$this->executeEnvironmentFiles();

			}

		}

		protected function executeEnvironmentFiles($file = false) {

			if (!$this->appPath->parent->has("../db/seeds/".$this->_environment)) {
				return;
			}

			if (!empty($file)) {

				if ($seedsFile = $this->appPath->parent->has("../db/seeds/".$this->_environment."/$file.php")) {
					$this->executeFile($seedsFile);
				}

			}
			else {

				$seeds = $this->appPath->parent->children("../db/seeds/$pick");
				foreach ($seeds as $seed) {
					$this->executeFile($seed);
				}

			}

		}

		protected function executeFile($file) {

			echo "Running $file.php\n";
			require_once $file;

		}

		protected function executeSeedFiles() {

			if ($seedsFile = $this->appPath->parent->chdir("../db/seeds")->has("seeds.php")) {
				$this->executeFile($seedsFile);
			}

			$this->executeEnvironmentFiles("seeds");

		}

	}

?>