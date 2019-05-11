<?php

namespace Aqua;

use Exception;

	class Binary extends Node {

		protected $_left;
		protected $_right;

		const Operators = [
			"=",
			"!=",
			">",
			">=",
			"<",
			"<=",
			"IN",
			"NOT IN",
			"IS",
			"IS NOT",
			"LIKE"
		];

		function __construct($left, $right, $operator = "Binary") {

			parent::__construct();

			if (empty($operator)) {
				throw new Exception("Operator cannot be empty", 1);
			}

			$this->_class = $operator;

			$this->_left = $left;
			$this->_right = $right;

			$this->attributeAccessible("left", "right");

		}

	}

?>