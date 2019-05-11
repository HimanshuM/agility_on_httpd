<?php

namespace Agility\Generators;

use Agility\Configuration;
use ArrayUtils\Arrays;
use FileSystem\Dir;
use FileSystem\File;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class TaskGenerator extends Base {

		protected $_code;
		public $taskName;
		public $filePath;
		protected $parentDir = "";
		protected $_methods = [];
		public $namespace = "";

		protected function __construct($appPath, $root, $args) {

			parent::__construct($appPath, $root, $args, "task");
			$this->_parseOptions($args);

		}

		private function _classify($task) {

			$namespace = "";
			if ($task->length > 1) {
				$namespace = implode("\\", $task->all(-1));
			}

			$taskName = $task->last;
			$this->taskName = $taskName;
			$this->_setNamespace($namespace);

		}

		protected function _generate() {

			parent::_generate();
			$this->_writeTask();

		}

		private function _getFilePathAndTaskClassName($task) {

			$filePath = new Arrays;
			$taskName = new Arrays;

			$components = explode("/", $task);
			foreach ($components as $index => $component) {

				$filePath[] = Str::snakeCase($component);
				$taskName[] = Str::camelCase($component);

			}

			if ($filePath->length > 0) {
				$this->parentDir = $filePath->firstFew(-1)->implode("/");
			}
			$this->filePath = $filePath->implode("/");

			return $this->_classify($taskName);

		}

		private function _getMethods($args) {

			foreach ($args as $arg) {

				if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $arg) && !in_array($arg, $this->_methods)) {
					$this->_methods[] = $arg;
				}

			}

		}

		protected function _parseOptions($args = []) {

			parent::_parseOptions($args);
			$this->_getFilePathAndTaskClassName($this->_args->shift);

			$this->_getMethods($args);

		}

		protected function _publish($template, $name, $data) {
			$this->_code = $data;
		}

		function renderTask() {

			foreach ($this->_methods as $method) {
				echo "\n\t\tfunction ".$method."() {\n\n\t\t}\n\n";
			}

		}

		private function _setNamespace($namespace) {

			if (!empty($namespace)) {
				$this->namespace = "\\".$namespace;
			}

		}

		static function start($appPath, $root, $args) {
			(new static($appPath, $root, $args))->_generate();
		}

		private function _writeTask() {

			if (!empty($this->parentDir)) {
				$this->_appRoot->mkdir("lib/tasks/".$this->parentDir);
			}

			$filePath = $this->_appRoot."/lib/tasks/".$this->filePath.".php";
			if ($this->overwrite || !file_exists($filePath)) {

				$controllerFile = File::open($filePath);
				$controllerFile->write($this->_code);

				$this->echo("\t#B##White#create  #N#lib/tasks/".$this->filePath.".php");

			}
			else if (file_exists($filePath)) {
				$this->echo("\t#B##LBlue#identical  #N#lib/tasks/".$this->filePath.".php");
			}

		}

	}

?>