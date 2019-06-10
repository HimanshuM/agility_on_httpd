<?php

namespace Agility\Data\Dynamic;

	class Call {

		protected $handled = false;
		public $args = [];

		function __construct($args = []) {
			$this->args = $args;
		}

		function handled() {
			return $this->handled = true;
		}

		function isHandled() {
			return $this->handled;
		}

	}

?>