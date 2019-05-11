<?php

namespace Agility\Data\Generators;

use Agility\Generators\Base;
use ArrayUtils\Arrays;
use FileSystem\File;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class ModelGenerator extends Base {

		protected $_code;
		public $model;
		protected $_filePath;
		protected $forceName = false;
		protected $migration = true;
		public $namespace = "";
		public $parent = true;
		protected $_useParent = false;
		protected $_parentClass = false;
		protected $_parentDir = false;
		protected $_tableName = false;
		public $primaryKeyType = true;

		function __construct($appPath, $root, $args) {

			parent::__construct($appPath, $root, $args, "model");
			$this->_parseOptions(["migration", "parent", "force-name", "primary-key-type"]);

		}

		private function _appHasApplicationModelClass() {
			return $this->_appRoot->has("app/models/application_model.php");
		}

		private function _classify($model) {

			if ($model->length > 1) {
				$this->namespace = "\\".$model->firstFew(-1)->implode("\\");
			}

			$model = $model->last;
			$this->model = $model;

		}

		protected function _generate() {

			parent::_generate();
			$this->_writeModel();
			$this->_generateMigration();

		}

		private function _generateMigration() {

			if ($this->migration && $this->_useParent) {

				$tableName = $this->_tableName;
				if ($tableName === false) {

					$tableName = Str::snakeCase($this->namespace./*"_".*/Inflect::pluralize($this->model));
					$tableName = str_replace("\\", "_", $tableName);

				}

				$tableName = trim(strtolower($tableName), "_");
				MigrationGenerator::start($this->_appPath, $this->_appRoot, $this->_args->prepend("create_".$tableName));

			}

		}

		private function _getFilePathAndModelClass($model) {

			$filePath = new Arrays;
			$modelName = new Arrays;

			$model = str_replace("\\", "/", $model);
			$components = explode("/", $model);
			foreach ($components as $index => $component) {

				if ($index == count($components) - 1 && !$this->forceName) {
					$component = Inflect::singularize($component);
				}

				$filePath[] = Str::snakeCase($component);
				$modelName[] = Str::camelCase($component);

			}

			$this->_parentDir = $filePath->firstFew(-1)->implode("/");

			$this->_filePath = $filePath->implode("/");
			$this->_classify($modelName);

		}

		function hasTableName() {

			if ($this->_useParent && $this->_tableName !== false) {
				return "\n\t\t".$this->model."::\$tableName = \"".($this->_tableName === true ? $this->_model : $this->_tableName)."\";";
			}

			return "";

		}

		function parentClass() {
			return $this->_parentClass === false ? "" : "extends ".($this->_parentClass)." ";
		}

		protected function _parseOptions($arr = []) {

			parent::_parseOptions($arr);

			$model = $this->_getFilePathAndModelClass($this->_args->shift);
			if ($this->parent === true) {
				$this->_setAppropriateParentClass();
			}
			else if ($this->parent !== false) {
				$this->_parentClass = str_replace("/", "\\", $this->parent);
			}

		}

		function primaryKeyType() {

			if ($this->_useParent && !$this->primaryKeyType) {
				return "\n\t\t".$this->model."::autoIncrementingPrimaryKey = false;\n";
			}

			return "";

		}

		function _publish($template, $name, $data) {
			$this->_code = $data;
		}

		private function _setAppropriateParentClass() {

			if ($this->_appHasApplicationModelClass()) {

				$this->parent = "App\\Models\\ApplicationModel";
				$this->_parentClass = "ApplicationModel";

			}
			else {

				$this->parent = "Agility\\Data\\Model";
				$this->_parentClass = "Model";

			}

			$this->_useParent = true;

		}

		private function _setNamespace($namespace) {

			if (!empty($namespace)) {
				$this->namespace = "\\".$namespace;
			}

		}

		function useParent() {

			$use = "";
			if ($this->_useParent || !empty($this->namespace)) {
				$use = "\nuse ".$this->parent.";\n";
			}

			return $use."\n";

		}

		private function _writeModel() {

			$filePath = $this->_appRoot."/app/models/".$this->_filePath.".php";
			if ($this->overwrite || !file_exists($filePath)) {

				if (!empty($this->_parentDir)) {
					$this->_appRoot->mkdir("app/models/".$this->_parentDir);
				}

				$modelFile = File::open($filePath);
				$modelFile->write($this->_code);

				$this->echo("\t#B##White#create  #N#app/models/".$this->_filePath.".php");

			}
			else if (file_exists($filePath)) {
				$this->echo("\t#B##LBlue#identical  #N#".$this->_filePath);
			}

		}

	}

?>