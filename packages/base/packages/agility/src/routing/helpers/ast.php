<?php

namespace Agility\Routing\Helpers;

use Agility\Routing\Route;
use ArrayUtils\Arrays;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	class Ast {

		protected $ast;

		function __construct() {
			$this->ast = new Arrays;
		}

		function addRoute($route) {

			$pathComponents = $this->pathComponents($route->normalizedPath);
			$leaf = $this->propagateLeaves($pathComponents);
			$leaf->append($route);

		}

		function appendSubtree($route, $ast) {

			if (!is_a($route, Route::class)) {
				throw new InvalidArgumentTypeException("Agility\\Routing\\Helpers\\Ast::appendSubtree", 0, "Agility\\Routing\\Route", gettype($route));
			}

			if (($leaf = $this->crawl($this->pathComponents($route->normalizedPath))) === false) {
				return false;
			}

			$leaf[] = $ast->ast;
			return true;

		}

		function crawl($pathComponents, $real = false) {

			$params = [];

			$leaf = &$this->ast;
			foreach ($pathComponents as $component) {

				if (is_numeric($component)) {
					$component = intval($component);
				}

				if (!isset($leaf[$component])) {

					if ($real) {

						if (isset($leaf[":param"])) {

							$params[] = $component;
							$component = ":param";

						}
						else {
							return [false, false];
						}

					}
					else {
						return false;
					}

				}

				$leaf = &$leaf[$component];

			}

			if ($real) {

				if (!isset($leaf[0])) {
					return [false, false];
				}

				return [$leaf[0], $params];

			}

			if (!isset($leaf[0])) {
				return false;
			}

			return $leaf[0];

		}

		function mergeTrees($ast) {

		}

		function pathComponents($route) {

			$components = new Arrays(explode("/", trim($route, "/")));
			return $components->prepend("/");

		}

		protected function propagateLeaves($pathComponents) {

			$leaf = &$this->ast;
			foreach ($pathComponents as $component) {

				// var_dump($component, isset($leaf[$component]));
				if (!isset($leaf[$component])) {
					$leaf[$component] = new Arrays;
				}

				$leaf = &$leaf[$component];

			}

			return $leaf;

		}

	}

?>