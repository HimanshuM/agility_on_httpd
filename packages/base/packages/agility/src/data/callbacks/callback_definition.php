<?php

namespace Agility\Data\Callbacks;

	class CallbackDefinition {

		public $callback;
		public $async = false;

		function __construct($callback, $async = false) {

			$this->callback = $callback;
			$this->async = $async;

		}

	}

?>