<?php

namespace Agility\Data\Migration;

use Agility\Data\Model;
use Arrayutils\Arrays;
use StringHelpers\Str;

	class SchemaMigration extends Model {

		protected static $primaryKey = "version";
		protected static $tableName = "schema_migrations";

		public $name;
		public $fileName;
		public $className;

		static function createTable() {

			(new CreateSchemaMigration)->processMigration();
			SchemaMigration::generateAttributes();

		}

		static function prepare($migrationFile) {

			$migration = new SchemaMigration;
			$migration->fileName = $migrationFile;
			$migration->setMeta();

			return $migration;

		}

		function setMeta() {

			$fileName = Arrays::split("_", $this->fileName);

			$this->version = $fileName->first;
			$this->name = $fileName->skip(1)->join;
			$this->className = Str::camelCase($this->name);

		}

	}

?>