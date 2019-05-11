<?php

namespace Aqua;

	class AndExpr extends Binary {

		function __construct($left, $right) {

			parent::__construct($left, $right);
			$this->_class = "And";

		}

	}

?>