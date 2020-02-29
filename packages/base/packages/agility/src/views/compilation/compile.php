<?php

namespace Agility\Views\Compilation;

use Agility\Configuration;
use Agility\Views\Application;

	class Compile {

		protected $application;
		protected $destination;
		protected $appJs;

		function __construct() {

			$entryPoint = Application::$entryPoint;
			$this->application = new $entryPoint;

			$this->destination = Configuration::documentRoot()->chdir("public/js");
			$this->appJs = $this->destination->touch("app.js");

		}

		protected function loadRoutes() {

			$routesFile = Configuration::documentRoot()->has(Application::$assetsBase."/routes.php");
			require_once($routesFile);

		}

		function toJS() {

			$this->appJs->write("");

		}

	}

?>