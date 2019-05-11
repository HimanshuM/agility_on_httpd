<?php

namespace Aqua;

use Exception;

	class Grouping extends Unary {

		function __construct($expr) {

			if (!is_subclass_of($expr, "Aqua\\Node")) {
				throw new Exception("Expression should be an object of class Node", 1);
			}

			parent::__construct($expr);
			$this->_class = "Grouping";

		}

	}

?>