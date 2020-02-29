<?php

namespace Agility\Views\Compilation;

	class Application {

		public $application;
		protected $template;
		public $jsContent;

		function __construct() {

			$this->initializeTemplate();

			$entryPoint = Application::$entryPoint;
			$this->application = new $entryPoint;

		}

		protected function initializeTemplate() {
			$this->template = new Template(FileSystem::open(__DIR__."/../js/templates"), $this);
		}

		protected function loadRoutes() {

			$routesFile = Configuration::documentRoot()->has(Application::$assetsBase."/routes.php");
			require_once($routesFile);

		}

		function toJS() {
			$this->jsContent = $this->template->load("base.js");
		}

		function constructorContent() {

		}

	}

?>