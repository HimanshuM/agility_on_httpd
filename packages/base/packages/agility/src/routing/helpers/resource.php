<?php

namespace Agility\Routing\Helpers;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use StringHelpers\Inflect;

	class Resource {

		use Accessor;

		const ApiActions = [
			"index",
			"create",
			"show",
			"update",
			"delete"
		];

		const HttpActions = [
			"index",
			"new",
			"create",
			"show",
			"edit",
			"update",
			"delete"
		];

		const ActionToMethod = [
			"index" => "get",
			"new" => "get",
			"create" => "post",
			"show" => "get",
			"edit" => "get",
			"update" => "patch",
			"delete" => "delete"
		];

		// Namespace of the controller
		protected $namespace;
		// Controller class
		protected $controller;
		// Path to replace the controller name
		protected $path;
		// Name to replace the route helper name
		protected $name;
		// Identifier parameter name
		protected $param = "id";
		// Nested routes should be shallow or not
		protected $shallow = false;
		// Only build API actions
		protected $apiOnly = false;
		// Only build these routes
		protected $only;
		// Skip these routes
		protected $except;
		// Constraints applicable to all the routes
		protected $constraints;
		// Defaults applicable to all the routes
		protected $defaults;

		// These routes will be built
		protected $actions;
		protected $singleton = false;

		// Required for sub routes
		protected $pathPrefix;

		function __construct($namespace, $controller, $path = "", $name = "", $param = "id", $shallow = false, $apiOnly = false, $only = [], $except = [], $constraints = [], $defaults = [], $pathPrefix = false) {

			$this->namespace = $namespace;
			$this->controller = $controller;
			$this->path = $path ?: $this->controller;
			$this->name = $name ?: $this->controller;
			$this->param = $param;
			$this->shallow = $shallow;
			$this->apiOnly = $apiOnly;

			if (!is_a($only, Arrays::class)) {
				$only = new Arrays($only);
			}
			$this->only = $only;

			if (!is_a($except, Arrays::class)) {
				$except = new Arrays($except);
			}
			$this->except = $except;
			$this->constraints = $constraints;
			$this->defaults = $defaults;

			$this->pathPrefix = $pathPrefix;

			$this->compileActions();

			$this->readonly("actions", "singleton", "namespace", "controller",  "path",  "name",  "param",  "shallow",  "only",  "except", "constraints", "defaults", "pathPrefix");

		}

		function defaultActions() {

			if ($this->apiOnly) {
				return new Arrays(Resource::ApiActions);
			}
			else {
				return new Arrays(Resource::HttpActions);
			}

		}

		function compileActions() {

			$this->actions = $this->defaultActions();

			if (!$this->only->empty) {
				$this->actions = $this->actions->intersect($this->only->array);
			}
			if (!$this->except->empty) {
				$this->actions = $this->actions->diff($this->except->array);
			}

		}

		function memberScope() {
			return $this->path."/:".$this->param;
		}

		function nestedParam() {
			return $this->shallow ? $this->memberScope() : $this->pathPrefix.($this->path."/:".$this->singular()."_".$this->param);
		}

		function plural() {
			return $this->path;
		}

		function singular() {
			return Inflect::singularize($this->path);
		}

	}

?>