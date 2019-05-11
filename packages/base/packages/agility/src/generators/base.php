<?php

namespace Agility\Generators;

use Agility\Console\Helpers\ArgumentsHelper;
use Agility\Console\Helpers\EchoHelper;
use Agility\Templating\Template;
use FileSystem\FileSystem;
use StringHelpers\Str;

	abstract class Base {

		use EchoHelper;

		protected $_args;
		protected $_options;

		protected $_appPath;
		protected $_appRoot;
		protected $_appName;
		protected $_templateRoot;

		protected $_templating;

		protected $overwrite = false;
		protected $quite = false;

		protected function __construct($appPath, $root, $args, $template) {

			$this->_args = $args;
			$this->_appPath = $appPath;
			$this->_appRoot = $root;
			$this->_getAppName();

			$this->_setTemplateRoot($template);
			$this->_initializeTemplatingEngine();

		}

		protected function _generate() {
			$this->_iterateThrough($this->_templateRoot);
		}

		protected function _getAppName() {

			if (!empty($this->_appPath)) {

				if ($this->_appPath->isDir()) {

					$this->_appRoot = $this->_appPath;
					$this->_appName = Str::camelCase($this->_appPath->basename);

				}
				else {

					$this->_appRoot = $this->_appPath->parent;
					$appName = $this->_appRoot->chdir("..")->basename;
					$this->_appName = Str::camelCase($appName);

				}

			}

		}

		protected function _initializeTemplatingEngine() {
			$this->_templating = new Template($this->_templateRoot, $this);
		}

		private function _iterateThrough($dir) {

			$children = $dir->children(false, true);
			foreach ($children as $child) {

				$data = "";
				if ($child->isFile()) {

					$name = substr($child->path, strlen($this->_templateRoot->cwd."/"));
					$data = $this->_templating->load($name);

				}
				else {
					$name = substr($child->cwd, strlen($this->_templateRoot->cwd."/"));
				}

				$this->_publish($child, $name, $data);

				if ($child->isDir()) {
					$this->_iterateThrough($child);
				}

			}

		}

		protected function _parseOptions($args = []) {

			$this->_options = ArgumentsHelper::parseOptions($this->_args, true);
			if ($this->_options->exists("--quite")) {
				$this->quite = true;
			}
			if ($this->_options->exists("--no-quite")) {
				$this->quite = false;
			}

			if ($this->_options->exists("--force")) {
				$this->overwrite = true;
			}
			if ($this->_options->exists("--skip")) {
				$this->overwrite = false;
			}

			if (!empty($args)) {

				foreach ($args as $arg) {

					$attr = Str::pascalCase($arg);
					if ($this->_options->exists("--".$arg)) {
						$this->$attr = true;
					}
					if ($this->_options->exists("--no-".$arg)) {
						$this->$attr = false;
					}

				}

			}

			$this->_unsetOptionsFromArgs();

		}

		function phpEchoTagOpen() {
			echo "<?=";
		}

		function phpTagClose() {
			echo "?>";
		}

		function phpTagOpen() {
			echo "<?php\n";
		}

		abstract protected function _publish($template, $name, $data);

		protected function _setTemplateRoot($template) {
			$this->_templateRoot = FileSystem::open(__DIR__."/templates/".$template);
		}

		static function start($appPath, $root, $args) {
			(new static($appPath, $root, $args, static::class))->_generate();
		}

		protected function _unsetOptionsFromArgs() {

			$keys = [];
			foreach ($this->_options as $key => $value) {
				$keys[] = $key.(!is_bool($value) ? "=".$value : "");
			}

			// $this->_args = $this->_args->diff($keys);

		}

	}

?>