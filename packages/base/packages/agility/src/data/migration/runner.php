<?php

namespace Agility\Data\Migration;

use Agility\Data\Exceptions\SqlException;
use ArrayUtils\Arrays;
use Exception;

	class Runner {

		protected $migrationsDir;
		protected $allMigrations;

		function __construct($migrationsDir) {

			$this->migrationsDir = $migrationsDir;
			$this->allMigrations = new Arrays;

		}

		protected function diffMigrations($previousMigrations) {
			return $this->allMigrations->map(":version")->diff($previousMigrations->map(":version"));
		}

		protected function executeMigration($migration) {

			$class = "\\Db\\Migrate\\".$migration->className;
			echo "Running ".$migration->name."\n";
			require_once $this->migrationsDir."/".$migration->fileName.".php";
			if (class_exists($class)) {

				try {

					$migrationObject = new $class;
					$migrationObject->processMigration();

					$migration->save();

				}
				catch (SqlException $e) {

					echo $e."\n";
					die($e->getTraceAsString()."\n");

				}
				catch (Exception $e) {

					echo $e->getMessage()."\n";
					die($e->getTraceAsString()."\n");

				}

			}

		}

		protected function executeMigrations($versions) {

			foreach ($versions as $version) {
				$this->executeMigration($this->allMigrations[$version]);
			}

			return $versions->length;

		}

		function executePendingMigrations() {

			if (empty($pending = $this->needsMigration())) {
				return 0;
			}

			return $this->executeMigrations($pending);

		}

		protected function listMigrations() {

			$allMigrations = $this->migrationsDir->children;
			foreach ($allMigrations as $migrationFile) {

				$migration = $this->migration($migrationFile);
				$this->allMigrations[$migration->version] = $migration;

			}

		}

		protected function migration($migrationFile) {
			return SchemaMigration::prepare($migrationFile->name);
		}

		protected function needsMigration() {

			try {
				$previousMigrations = SchemaMigration::all();
			}
			catch (Exception $e) {

				SchemaMigration::createTable();
				$previousMigrations = new Arrays;

			}

			$this->listMigrations();

			return $this->diffMigrations($previousMigrations);

		}

		protected function runMigration() {

		}

	}

?>