<?php

namespace Agility\Routing\Helpers;

	trait Defaults {

		function defaults($arr, $callback) {

			$options["defaults"] = $arr;
			$this->processSubRoutes($options, $callback);

		}

		function constraints($arr, $callback) {

			$options["constraints"] = $arr;
			$this->processSubRoutes($options, $callback);

		}

	}

?>