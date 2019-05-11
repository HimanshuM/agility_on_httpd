<?php

namespace Aqua;

use Exception;

	class On extends Unary {

		function __construct(Where $binary) {

			if (!is_a($binary, "Aqua\\Where")) {
				throw new Exception("Invalid On clause specified", 1);
			}

			parent::__construct($binary);
			$this->_class = "On";

		}

	}

?>