<?php

namespace Aqua\Visitors;

	trait Sanitizer {

		function sanitize($name) {
			return "`".$name."`";
		}

	}

?>