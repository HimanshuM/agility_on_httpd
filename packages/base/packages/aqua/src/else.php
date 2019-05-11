<?php

namespace Aqua;

	class Else extends Unary {

		function __construct($expr) {

			parent::__construct($expr);
			$this->_class = "Else";

		}

	}

?>