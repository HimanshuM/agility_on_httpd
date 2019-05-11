<?php

namespace Agility\Generators;

use Agility\Configuration;
use ArrayUtils\Arrays;
use FileSystem\Dir;
use FileSystem\File;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class ControllerGenerator extends Base {

		protected $_code;
		public $controller;
		public $filePath;
		protected $parentDir = "";
		protected $_methods = [/*"index", "show", "update", "delete"*/];
		public $namespace = "";
		public $parentClass;
		protected $_viewPath;

		public $scaffold = false;
		public $scaffoldObject = false;
		public $scaffoldObjects = false;

		protected $views = true;
		protected $skipRoutes = false;

		public $apiOnly = false;

		protected function __construct($appPath, $root, $args, $scaffold) {

			$template = "controller";
			if ($this->apiOnly = Configuration::apiOnly()) {
				$template = "api_controller";
			}

			parent::__construct($appPath, $root, $args, $template);
			$this->scaffold = $scaffold;
			$this->_parseOptions();

		}

		protected function _appHasApplicationControllerClass() {
			return $this->_appRoot->has("app/controllers/application_controller.php");
		}

		private function _classify($controller) {

			$namespace = "";
			if ($controller->length > 1) {
				$namespace = implode("\\", $controller->all(-1));
			}

			$controllerName = $controller->last;
			if ($this->scaffold) {

				$controllerName = Inflect::pluralize($controllerName);
				$this->scaffold = Inflect::singularize($controllerName);
				$this->scaffoldObject = lcfirst($this->scaffold);
				$this->scaffoldObjects = Inflect::pluralize($this->scaffoldObject);

			}

			$this->controller = $controllerName;
			$this->_setNamespace($namespace);

		}

		protected function _generate() {

			parent::_generate();

			$this->_writeController();
			$this->_writeViews();
			$this->_writeRoute();

		}

		private function _getFilePathAndControllerClassName($controller) {

			$filePath = new Arrays;
			$controllerName = new Arrays;

			$components = explode("/", $controller);
			foreach ($components as $index => $component) {

				if ($this->scaffold && $index == count($components) - 1) {
					$component = Inflect::pluralize($component);
				}

				$filePath[] = Str::snakeCase($component);
				$controllerName[] = Str::camelCase($component);

			}

			if ($filePath->length > 0) {
				$this->parentDir = $filePath->firstFew(-1)->implode("/");
			}
			$this->filePath = $filePath->implode("/");

			return $this->_classify($controllerName);

		}

		private function _getMethods($args) {

			foreach ($args as $arg) {

				if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $arg) && !in_array($arg, $this->_methods)) {
					$this->_methods[] = $arg;
				}

			}

		}

		private function _getRoutesCode() {

			$routesCode = [];
			if ($this->scaffold) {
				$routesCode[] = "\$this->resources(\"".$this->filePath."\");";
			}

			foreach ($this->_methods as $method) {
				$routesCode[] = "\$this->get(\"".$this->filePath."/".$method."\");";
			}

			return $routesCode;

		}

		private function _getValidRoutesCode($lines) {

			$finalCode = [];

			$trimmedLines = [];
			foreach ($lines as $line) {
				$trimmedLines[] = trim($line);
			}

			$newRoute = $this->_getRoutesCode();
			foreach ($newRoute as $route) {

				if (!in_array($route, $trimmedLines)) {
					$finalCode[] = $route;
				}

			}

			if (empty($finalCode)) {
				return "";
			}

			return "\n\t\t".implode("\n\t\t", $finalCode)."\n\n";

		}

		protected function _identifyParent() {

			if ($this->_appHasApplicationControllerClass()) {
				$this->parentClass = "ApplicationController";
			}
			else {
				$this->parentClass = "Http\\".($this->apiOnly ? "ApiController" : "Controller");
			}

		}

		protected function _parseOptions($args = []) {

			parent::_parseOptions(["views", "skip-routes"]);
			$controllerName = $this->_getFilePathAndControllerClassName($this->_args->shift);

			$this->_identifyParent();

			$this->_getMethods($args);

		}

		protected function _publish($template, $name, $data) {
			$this->_code = $data;
		}

		function renderMethods() {

			foreach ($this->_methods as $method) {
				echo "\t\tfunction ".$method."() {\n\n\t\t}\n\n";
			}

		}

		private function _setNamespace($namespace) {

			if (!empty($namespace)) {
				$this->namespace = "\\".$namespace;
			}

		}

		static function start($appPath, $root, $args, $scaffold = false) {
			(new static($appPath, $root, $args, $scaffold))->_generate();
		}

		function useNamespace() {

			if ($this->parentClass == "ApplicationController" && !empty($this->namespace)) {
				return "use App\\Controllers\\ApplicationController\n";
			}
			else if ($this->parentClass != "ApplicationController") {
				return "use Agility\\Http\n";
			}

			return "";

		}

		private function _writeController() {

			if (!empty($this->parentDir)) {
				$this->_appRoot->mkdir("app/controllers/".$this->parentDir);
			}

			$filePath = $this->_appRoot."/app/controllers/".$this->filePath."_controller.php";
			if ($this->overwrite || !file_exists($filePath)) {

				$controllerFile = File::open($filePath);
				$controllerFile->write($this->_code);

				$this->echo("\t#B##White#create  #N#app/controllers/".$this->filePath."_controller.php");

			}
			else if (file_exists($filePath)) {
				$this->echo("\t#B##LBlue#identical  #N#app/controllers/".$this->filePath."_controller.php");
			}

		}

		private function _writeRoute() {

			if ($this->skipRoutes || empty($this->_methods) && $this->scaffold == false) {
				return;
			}

			$routesFile = File::open($this->_appRoot."/config/routes.php");
			$routesContent = $routesFile->lines();
			$routesCode = $this->_getValidRoutesCode($routesContent);

			if (empty($routesCode)) {

				$this->echo("\t#B##White#invoke  #N#resource_route");
				$this->echo("\t #Gray#route    #N#resource '".$this->filePath."'");

				return;

			}

			array_splice($routesContent, -4, 1, $routesCode);
			$routesFile->write(implode("", $routesContent));

			$this->echo("\t#B##White#invoke  #N#resource_route");
			$this->echo("\t route    resource '#N#".$this->filePath."'");

		}

		private function _writeView($view) {

			$view .= ".php";
			if ($this->_viewPath->has($view)) {

				$this->echo("\t#B##LBlue#identical    #N#app/views/".$this->filePath."/".$view);
				return;

			}

			$this->_viewPath->touch($view);
			$this->echo("\t#B#create    #N#app/views/".$this->filePath."/".$view);

		}

		private function _writeViews() {

			if (!$this->views || $this->apiOnly) {
				return;
			}

			$this->_viewPath = Dir::path($this->_appRoot."/app/views/");
			$this->_viewPath->mkdir($this->filePath);
			$this->_viewPath->chdir($this->filePath);

			$defaultRoutes = ["index", "show"];
			$methods = array_merge($this->_methods, $defaultRoutes);

			foreach ($methods as $method) {
				$this->_writeView($method);
			}

		}

	}

?>