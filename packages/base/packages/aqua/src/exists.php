<?php

namespace Aqua;

	class Exists extends Binary {

		function __construct(SelectStatement $statement, bool $yes = true) {

			if (!is_a($statement, "Aqua\\SelectStatement")) {
				throw new Exception("Invalid Exists clause specified", 1);
			}

			parent::__construct($statement, $yes);
			$this->_class = "Exists";

		}

	}

?>