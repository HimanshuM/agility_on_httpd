<?php

namespace Aqua;

	class When extends Binary {

		function __construct($left, $right) {

			parent::__construct($left, $right);
			$this->_class = "When";

		}

	}

?>