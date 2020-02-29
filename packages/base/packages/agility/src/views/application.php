<?php

namespace Agility\Views;

	class Application {

		protected $bootstrampComponent;

		static $assetsBase = "app/assets";
		static $viewsBase = "views";
		static $entryPoint = App\Assets\Index::class;
		static $baseView = "layout/index.php";

		function bootstrap($component) {
			$this->bootstrampComponent = $component;
		}

	}