<?php

namespace Agility\Views;

	class Routes {

		static $routes = [];

		static function draw($routes = []) {
			Routes::$routes = $routes;
		}

	}