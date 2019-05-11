<?php

namespace Agility\Routing;

use Agility\Configuration;
use ArrayUtils\Arrays;

	class Routes {

		private static $ast;
		private static $domains;

		static function addDomain($domain = "/") {

			if (empty(Routes::$domains)) {
				Routes::$domains = new Arrays;
			}

			if (!Routes::$domains->exists($domain)) {
				Routes::$domains[$domain] = new MethodTrees;
			}

			return Routes::$domains[$domain];

		}

		static function ast() {

			if (empty(Routes::$ast)) {
				Routes::$ast = new MethodTrees;
			}

			return Routes::$ast;

		}

		static function domains() {
			return Routes::$domains;
		}

		static function draw($callback) {
			Routes::invokeCallback("\\App\\Controllers\\", $callback);
		}

		static function initialize() {

			if (($file = Configuration::documentRoot()->has("config/routes.php")) === false) {
				throw new Exceptions\RoutesNotFoundException;
			}

			require_once $file;
			return true;

		}

		static function inspect($verb = false) {

			$verb = $verb ? [$verb] : ["get", "post", "put", "patch", "delete"];
			foreach ($verb as $method) {
				var_dump(Routes::$domains["/"]->$method);
			}

		}

		/*static function invokeCallback($rootNamespace, $callback) {

			Routes::ast();
			$builder = new Builder($rootNamespace, Routes::$ast);
			($callback->bindTo($builder, $builder))();

		}*/
		static function invokeCallback($rootNamespace, $callback, $domain = "/") {

			Routes::addDomain($domain);
			$builder = new Builder($rootNamespace, Routes::$domains[$domain]);
			($callback->bindTo($builder, $builder))();

		}

	}

?>