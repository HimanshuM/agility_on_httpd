<?php

namespace Agility\Data\Generators;

use Agility\Generators\Base;
use Agility\Data\Schema\Attribute;
use FileSystem\File;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class MigrationGenerator extends Base {

		protected $_migrationName;
		protected $_migrationPath;
		public $className;
		public $tableName;
		public $type = false;
		public $timestamps = true;
		protected $_cannotProceed = false;
		public $primaryKeyType = true;

		public $attributes = [];
		public $attributesWithIndex = [];

		protected $_overwriting = false;

		function __construct($appPath, $root, $args) {

			parent::__construct($appPath, $root, $args, "migration");

			$this->_parseOptions(["timestamps", "primary-key-type"]);

		}

		protected function _generate() {

			if ($this->_cannotProceed) {
				return;
			}

			parent::_generate();

			$this->_writeMigration();

		}

		protected function _getAttributes() {

			$attributes = [];
			foreach ($this->_args as $arg) {

				$attrProps = explode(":", $arg);
				if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $attrProps[0]) && !isset($attributes[$attrProps[0]])) {
					$attributes[$attrProps[0]] = $attrProps;
				}

			}

			return $attributes;

		}

		protected function _parseOptions($arr = []) {

			parent::_parseOptions($arr);

			$this->_prepareMigration($this->_args->shift);
			if (!$this->_validateMigration()) {
				$this->_cannotProceed = true;
			}
			$this->_prepareAttributes();

		}

		protected function _prepareAttributes() {

			$attributes = $this->_getAttributes();
			foreach ($attributes as $name => $attrProps) {

				$attribute = Attribute::build($name, $attrProps);
				$this->attributes[] = $attribute;

				if ($attribute->indexed || $attribute->unique) {
					$this->attributesWithIndex[] = $attribute;
				}

			}

		}

		protected function _prepareMigration($migration) {

			$migration = Str::snakeCase($migration);

			if (strpos($migration, "create") !== false) {

				$this->tableName = Inflect::pluralize(substr($migration, strlen("create_")));
				$this->type = "create";

			}
			else if (strpos($migration, "add") === 0 || strpos($migration, "remove") === 0) {
				$this->_processEdit($migration);
			}

			$this->_migrationName = $migration;
			$this->_setMigrationFileName();
			$this->className = Str::camelCase($migration);

		}

		protected function _processEdit($migration) {

			$matches = [];
			$this->type = false;
			$tableName;
			if (preg_match("/^(add|remove)_\w+_(to|from)_(\w+)?/", $migration, $matches)) {

				$this->type = $matches[1];
				$tableName = $matches[3];
				$this->tableName = Inflect::pluralize($tableName);

				preg_match("/^".$this->type."_(\w+)_".$matches[2]."_$tableName$/", $migration, $matches);

				$attributes = explode("_", $matches[1]);
				foreach ($attributes as $attribute) {

					if (!isset($this->attributes[$attribute])) {

						$attribute = Attribute::build($attribute, [$attribute]);
						$this->attributes[] = $attribute;

					}

				}

			}

		}

		function _publish($template, $name, $data) {
			$this->_code = $data;
		}

		protected function _setMigrationFileName() {
			$this->_migrationFileName = "db/migrate/".date("YmdHis_").$this->_migrationName.".php";
		}

		protected function _validateMigration() {

			$this->_appRoot->mkdir("db/migrate");
			$this->_appRoot->chdir("db/migrate");
			$migrations = $this->_appRoot->children;
			$overwrite = false;
			foreach ($migrations as $migrationFile) {

				$migrationName = substr($migrationFile->basename, strpos($migrationFile->basename, "_") + 1);
				if ($this->_migrationName.".php" == $migrationName) {

					if (!$this->overwrite) {

						$this->echo("\t#B##Red#critical  #N#".$this->_migrationFileName, true);
						$this->echo("Another migration by name ".$this->_migrationName." already exists: db/migrate/".$migrationFile->basename, true);
						$this->echo("Use '--force' to overwrite this migration.", true);

						return false;

					}
					else {

						// $this->echo("\t#B##Blue#overwrite    #N#db/migrate/".$migrationFile->basename);
						$overwrite = $migrationFile;
						$this->_overwriting = true;
						break;

					}

				}

			}

			if (!empty($overwrite)) {
				$overwrite->delete();
			}

			return true;

		}

		protected function _writeMigration() {

			$file = File::open($this->_migrationFileName);
			$file->write($this->_code);

			if (!$this->_overwriting) {
				$this->echo("\t#B##White#create  #N#".$this->_migrationFileName);
			}
			else {
				$this->echo("\t#B##Blue#overwrite    #N#db/migrate/".$this->_migrationFileName);
			}

		}

	}

?>