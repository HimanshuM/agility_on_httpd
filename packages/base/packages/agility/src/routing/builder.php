<?php

namespace Agility\Routing;

use Agility\Configuration;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use Closure;
use StringHelpers\Str;

	class Builder {

		use Accessor;
		use Helpers\Defaults;
		use Helpers\Match;
		use Helpers\Resources;
		use Helpers\Scope;
		use Helpers\Verbs;

		protected $namespace;
		protected $controller = false;
		protected $pathPrefix = "/";
		protected $parameters;
		protected $constraints;
		protected $defaults;
		protected $routeNamePrefix = "";
		protected $apiOnly;
		protected $ast;
		protected $parentResource = false;

		function __construct($rootNamespace, $ast) {

			$this->constraints = new Arrays;
			$this->defaults = new Arrays;
			$this->parameters = new Arrays;
			$this->ast = $ast;
			$this->apiOnly = Configuration::apiOnly();

			$this->readonly("ast");

			$this->setRootNamespace($rootNamespace);

		}

		static function cleanPath($path) {
			return trim($path, "/ ");
		}

		protected function compileOptions($options) {

			if (!empty($options["namespace"])) {
				$this->namespace .= Str::normalize($options["namespace"])."\\";
			}

			if (!empty($options["controller"])) {
				$this->controller = $options["controller"];
			}

			if (!empty($options["path"])) {
				$this->pathPrefix = "/".trim(strtolower($options["path"]), "/ ")."/";
			}

			if (!empty($options["constraints"])) {

				if (!is_a($options["constraints"], Arrays::class)) {
					$options["constraints"] = new Arrays($options["constraints"]);
				}
				$this->constraints = $options["constraints"];

			}

			if (!empty($options["defaults"])) {

				if (!is_a($options["defaults"], Arrays::class)) {
					$options["defaults"] = new Arrays($options["defaults"]);
				}
				$this->defaults = $options["defaults"];

			}

			if (!empty($options["parameters"])) {

				if (!is_a($options["parameters"], Arrays::class)) {
					$options["parameters"] = new Arrays($options["parameters"]);
				}
				$this->parameters = $options["parameters"];

			}
			if (!empty($options["parentResource"])) {
				$this->parentResource = $options["parentResource"];
			}

		}

		protected function constructRoute($verb, $route, $handler, $options, $action = "") {

			list($controller, $action) = $this->identifyHandler($handler, $action);
			if (empty($controller) && empty($action)) {
				throw new Exceptions\UndefinedRouteActionException($route);
			}

			$path = $this->preparePath($route);
			list($normalizedPath, $parameters) = $this->prepareParameters($route);
			$options = $this->prepareOptions($options, $parameters);

			$route = new Route($options["namespace"], $controller, $action, $verb, $path, $normalizedPath, $parameters, $options["constraints"], $options["defaults"]);
			$this->ast->$verb->addRoute($route);

			return $route;

		}

		protected function constructSubRouteBuilder($resource, $resourceMembers = false) {

			$options = [];
			if (is_a($resource, Helpers\Resource::class)) {

				if ($resourceMembers) {

					$options["controller"] = $resource->controller;
					$options["path"] = $resource->pathPrefix.$resource->memberScope();

				}
				else {
					$options["path"] = $resource->nestedParam();
				}

				$options["parameters"] = $resource->param;
				$options["constraints"] = $resource->constraints;
				$options["defaults"] = $resource->defaults;
				if ($resource->namespace != "\\App\\Controllers\\") {
					$options["namespace"] = $resource->namespace;
				}
				$options["parentResource"] = $resource;

			}
			else if (is_a($resource, Route::class)) {

				$options["path"] = $resource->path;
				$options["parameters"] = $resource->parameters;
				$options["constraints"] = $resource->constraints;
				$options["defaults"] = $resource->defaults;
				if ($resource->namespace != "\\App\\Controllers\\") {
					$options["namespace"] = $resource->namespace;
				}

			}
			else if (is_array($resource)) {
				$options = $resource;
			}

			$builder = new Builder($this->namespace, $this->ast);
			$builder->compileOptions($options);

			return $builder;

		}

		protected function identifyHandler($handler, $action) {

			$controller = $handler;
			if (is_a($handler, Closure::class)) {

				$controller = "";
				$action = $handler;

			}
			else if (is_string($handler) && strpos($handler, "#") !== false) {
				list($controller, $action) = explode("#", $handler);
			}
			else if (!empty($this->controller)) {

				$controller = $this->controller;
				if (empty($action)) {
					$action = $handler;
				}

			}
			else if (empty($action)) {
				return [false, false];
			}

			return [$controller, $action];

		}

		protected function parseArguments($args) {

			$route = false;
			$handler = false;
			$options = false;
			$callback = false;

			foreach ($args as $arg) {

				if (is_string($arg)) {

					if ($route === false) {
						$route = $arg;
					}
					else if ($handler === false) {
						$handler = $arg;
					}

				}
				else if (is_array($arg)) {

					if ($options === false) {
						$options = $arg;
					}

				}
				else if (is_a($arg, Closure::class) && $callback === false) {
					$callback = $arg;
				}

			}

			return [$route, $handler, $options, $callback];

		}

		protected function prepareOptions($options, $parameters = []) {

			$return["namespace"] = $options["namespace"] ?? $this->namespace;
			$return["path"] = $options["path"] ?? $this->pathPrefix;
			$return["constraints"] = $this->constraints;
			if (!empty($options["constraints"])) {

				if (!is_a($options["constraints"], Arrays::class)) {
					$options["constraints"] = new Arrays($options["constraints"]);
				}
				$return["constraints"] = $options["constraints"];

			}
			$return["defaults"] = $this->defaults;
			if (!empty($options["defaults"])) {

				if (!is_a($options["defaults"], Arrays::class)) {
					$options["defaults"] = new Arrays($options["defaults"]);
				}
				$return["defaults"] = $options["defaults"];

			}

			foreach ($parameters as $param) {

				$param = trim($param, ":");
				if (isset($options[$param])) {
					$return["constraints"][$param] = $options[$param];
				}

			}

			return $return;

		}

		protected function prepareParameters($route) {

			$normalizedRoute = [];
			$parameters = [];

			$route = trim($this->pathPrefix.$route, "/");
			$components = explode("/", $route);
			foreach ($components as $component) {

				if (!empty($component) && $component[0] == ":") {

					$parameters[] = substr($component, 1);
					$component = ":param";

				}

				$normalizedRoute[] = $component;

			}

			// $parameters = array_merge($parameters, $this->parameters->array);

			return [implode("/", $normalizedRoute), $parameters];

		}

		protected function preparePath($path) {
			return $this->pathPrefix.(Builder::cleanPath($path));
		}

		protected function processSubRoutes($resource, $callback = null, $resourceMembers = false) {

			if (empty($callback)) {
				return;
			}

			if (!is_callable($callback)) {
				throw new InvalidSubRouteCallbackException($path);
			}

			$builder = $this->constructSubRouteBuilder($resource, $resourceMembers);
			($callback->bindTo($builder))();

			// $route = false;
			// if (is_a($resource, Resource::class)) {
			// 	// This is incomplete.
			// 	// GET resource/:id route should go here
			// 	$route = $resource;
			// }
			// else if (is_a($resource, Route::class)) {
			// 	$route = $resource;
			// }

			// $ast = $builder->ast;
			// $this->ast->appendSubtree($route, $ast);

		}

		protected function setRootNamespace($rootNamespace) {
			$this->namespace = $rootNamespace;
		}

	}

?>