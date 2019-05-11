<?php

namespace Aqua;

	class Case extends Node {

		protected $_case;
		protected $_when = [];
		protected $_else;

		function __construct($expr = []) {

			parent::__construct();

			$this->_class = "Case";
			if (!is_string($expr) && !is_a($expr, "Aqua\\Attribute")) {
				throw new Exception("Case expression must be a string or an object of type Aqua\\Attribute", 1);
			}

			$this->_case = $expr;

		}

		function when($expr, $then = null) {
			$this->_when[] = new When($expr, $then);
		}

		function then($expr) {

			$last = $this->_when[count($this->_when) - 1];
			$last->right = $expr;

		}

		function else($expr) {
			$this->_else = new Else($expr);
		}

	}

?>