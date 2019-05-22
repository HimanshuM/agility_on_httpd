<?php

namespace Workflow;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	class State {

		use Accessor;

		protected $name;
		protected $events;

		function __construct($name) {

			$this->name = $name;
			$this->events = new Arrays;

			$this->readonly("name", "events");

		}

		function allEvents() {
			return $this->events->keys;
		}

		function event($name, $transitionsTo = false, $action = false) {

			$event = new Event;
			$event->name = $name;
			$event->transitionsTo = $transitionsTo;
			$event->action = $action;

			$this->events[$name] = $event;

		}

		function __toString() {
			return $this->name;
		}

	}

?>