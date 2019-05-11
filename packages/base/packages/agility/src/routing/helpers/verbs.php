<?php

namespace Agility\Routing\Helpers;

use Closure;

	trait Verbs {

		function delete($path, $handler, $options = []) {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("delete", $route, $handler, $options);

		}

		function get() {

			$action = false;
			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			if (empty($handler) && !empty($callback)) {

				$action = $callback;
				$callback = null;

			}

			$route = $this->constructRoute("get", $route, $handler, $options, $action);
			$this->processSubRoutes($route, $callback);

		}

		function head($path, $handler, $options = []) {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("head", $route, $handler, $options);

		}

		function options($path, $handler, $options = []) {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("options", $route, $handler, $options);

		}

		function patch() {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("patch", $route, $handler, $options, $callback);

		}

		function post($path, $handler, $options = []) {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("post", $route, $handler, $options);

		}

		function put($path, $handler, $options = []) {

			list($route, $handler, $options, $callback) = $this->parseArguments(func_get_args());
			$this->constructRoute("put", $route, $handler, $options);

		}

	}

?>