<?php

namespace Workflow;

use Closure;

	class Event {

		public $name;
		public $transitionsTo = false;
		public $action = false;

		function invoke($object) {

			$closure = function($transitionsTo, $action) {

				$this->workflowState = $transitionsTo;

				if (!empty($action)) {

					if (is_a($action, Closure::class)) {
						($action->bindTo($this, $this))();
					}
					else {
						$this->$action();
					}

				}

				$this->save();

			};

			($closure->bindTo($object, $object))($this->transitionsTo, $this->action);

		}

	}

?>