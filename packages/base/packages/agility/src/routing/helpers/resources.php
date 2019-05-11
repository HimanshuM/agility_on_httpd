<?php

namespace Agility\Routing\Helpers;

use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	trait Resources {

		protected function constructResource($resource, $options = [], $callback = null) {

			$controller = $options["controller"] ?? $resource;
			$path = $options["path"] ?? $resource;
			$name = $options["name"] ?? "";
			$shallow = $options["shallow"] ?? false;
			$param = $options["param"] ?? "id";
			$only = $options["only"] ?? [];
			$except = $options["except"] ?? [];
			$apiOnly = boolval($options["apiOnly"] ?? $this->apiOnly);

			$options = $this->prepareOptions($options);
			$resource = new Resource($this->namespace, $controller, $path, $name, $param, $shallow, $apiOnly, $only, $except, $options["constraints"], $options["defaults"], $this->pathPrefix);
			foreach ($resource->actions as $action) {

				$path = $resource->path;
				if ($action == "new") {
					$path .= "/new";
				}
				else if (in_array($action, ["show", "update", "delete", "edit"])) {

					$path = $resource->memberScope();
					if ($action == "edit") {
						$path .= "/edit";
					}

				}

				$route = $this->constructRoute(Resource::ActionToMethod[$action], $path, $resource->controller, $options, $action);
				// if ($action == "show") {
				// 	$this->processSubRoutes($resource, $callback);
				// }

			}

			$this->processSubRoutes($resource, $callback);

		}

		protected function initiateResourceConstruction($args, $singleton = false) {

			$resources = [];
			$options = [];
			$callback = null;

			foreach ($args as $arg) {

				if (is_array($arg)) {
					$options = $arg;
				}
				else if (is_a($arg, "Closure")) {
					$callback = $arg;
				}
				else if (is_string($arg)) {
					$resources[] = $arg;
				}

			}

			if (count($resources) > 1 && (!empty($options) || !empty($callback))) {
				throw new Exception("Invalid argument combination");
			}

			foreach ($resources as $resource) {
				$this->constructResource($resource, $options, $callback, $singleton);
			}

		}

		function member($callback) {

			if (empty($this->parentResource)) {
				throw new MemberNotPartOfResourceException();
			}

			$this->processSubRoutes($this->parentResource, $callback, true);

		}

		function resources() {
			$this->initiateResourceConstruction(func_get_args());
		}

		// Creates a singleton resource
		function resource() {
			$this->initiateResourceConstruction(func_get_args(), true);
		}

	}

?>