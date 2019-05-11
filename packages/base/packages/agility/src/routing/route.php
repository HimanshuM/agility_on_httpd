<?php

namespace Agility\Routing;

use AttributeHelper\Accessor;

	class Route {

		use Accessor;

		// Namespace the controller sits inside
		protected $namespace;
		// Controller class
		protected $controller;
		// Action that serves the request
		protected $action;

		// HTTP verb the route responds to
		protected $verb;
		// Original path which is served by this route object
		protected $path;
		// Path with dynamic parameters replaced with :param placeholder
		protected $normalizedPath;
		// Dynamic parameters of the route
		protected $parameters;

		// Constraints applied to a route or its parameters
		protected $constraints;
		// Defaults to use when a given value (header, parameter, etc) is not specified
		protected $defaults;

		function __construct($namespace, $controller, $action, $verb, $path, $normalizedPath, $parameters, $constraints, $defaults) {

			$this->namespace = $namespace;
			$this->controller = $controller;
			$this->action = $action;
			$this->verb = $verb;
			$this->path = $path;
			$this->normalizedPath = $normalizedPath;
			$this->parameters = $parameters;
			$this->constraints = $constraints;
			$this->defaults = $defaults;

			$this->readonly("namespace", "controller", "action", "verb", "path", "normalizedPath", "parameters", "constraints", "defaults");

		}

		function __debugInfo() {

			return [
				"namespace" => $this->namespace,
				"controller" => $this->controller,
				"action" => $this->action,
				"verb" => $this->verb,
				"path" => $this->path,
				"normalizedPath" => $this->normalizedPath,
				"parameters" => $this->parameters,
				"constraints" => $this->constraints,
				"defaults" => $this->defaults
			];

		}

	}

?>