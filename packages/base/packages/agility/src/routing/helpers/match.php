<?php

namespace Agility\Routing\Helpers;

use Agility\Routing\Builder;
use Agility\Routing\Routes;
use Closure;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	trait Match {

		function domain($domainName, $options = [], $callback) {

			if (!is_a($callback, Closure::class)) {
				throw new InvalidArgumentTypeException("Routes::domain()", 3, "Closure", gettype($callback));
			}

			$domainAst = Routes::addDomain($domainName);

			$builder = new Builder("\\App\\Controllers\\", $domainAst);
			$builder->compileOptions($options);

			($callback->bindTo($builder, $builder))();

		}

		function match($verbs, $path, $handler, $options = []) {

			$verbs = array_intersect(["delete", "get", "head", "options", "patch", "post", "put"], $verbs);
			foreach ($verbs as $verb) {
				$this->constructRoute($verb, $path, $handler, $options);
			}

		}

		function root($handler) {
			$this->constructRoute("get", "/", $handler, []);
		}

	}

?>