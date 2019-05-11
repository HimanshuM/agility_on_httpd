<?php

namespace Agility\Data\Types;

	class Uint extends Base {

		function __construct($limit = null) {

			parent::__construct();
			$this->limit = $limit;

		}

		function cast($value) {
			return abs($value);
		}

		function serialize($value) {
			return abs($value);
		}

		function __toString() {
			return "uint";
		}

	}

?>