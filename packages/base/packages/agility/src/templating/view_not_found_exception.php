<?php

namespace Agility\Templating;

use Exception;

	class ViewNotFoundException extends Exception {

		function __construct($templateName, $partial = false) {
			parent::__construct("Could not find ".($partial ? "partial" : "")." view '$templateName'.");
		}

	}

?>