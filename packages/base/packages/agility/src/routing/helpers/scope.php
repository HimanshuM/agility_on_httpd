<?php

namespace Agility\Routing\Helpers;

use Agility\Routing\Exceptions\CallbackNotSpecifiedException;

	trait Scope {

		function controller($name, $arg1, $arg2 = false) {

			list($route, $handler, $options, $callback) = $this->parseArguments([$arg1, $arg2]);
			if (empty($callback)) {
				throw new CallbackNotSpecifiedException("controller");
			}

			$options["controller"] = $name;
			$options["path"] = $this->pathPrefix;

			return $this->processSubRoutes($options, $callback);

		}

		function namespace($name, $arg1, $arg2 = false) {

			list($route, $handler, $options, $callback) = $this->parseArguments([$arg1, $arg2]);
			if (empty($callback)) {
				throw new CallbackNotSpecifiedException("namespace");
			}

			$options["namespace"] = $name;
			if (!isset($options["path"])) {
				$options["path"] = $name;
			}

			return $this->processSubRoutes($options, $callback);

		}

		function scope($options, $callback) {
			return $this->processSubRoutes($options, $callback);
		}

	}

?>