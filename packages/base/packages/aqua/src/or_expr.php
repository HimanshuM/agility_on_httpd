<?php

namespace Aqua;

	class OrExpr extends Binary {

		function __construct($left, $right) {

			parent::__construct($left, $right);
			$this->_class = "Or";

		}

	}

?>