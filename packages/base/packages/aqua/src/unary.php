<?php

namespace Aqua;

	abstract class Unary extends Node {

		protected $_expr;

		function __construct($expr = null) {

			parent::__construct();

			$this->_class = "Unary";

			$this->_expr = $expr;

			$this->attributeAccessible("expr");

		}

	}

?>