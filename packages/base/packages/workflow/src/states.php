<?php

namespace Workflow;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	class States {

		use Accessor;

		protected $stateNames;
		protected $states;
		protected $eventNames;

		function __construct() {

			$this->stateNames = new Arrays;
			$this->states = new Arrays;
			$this->eventNames = new Arrays;

			$this->readonly("states");
			$this->methodsAsProperties();

		}

		protected function add($name, $state) {

			$this->stateNames[] = $name;
			$this->states[$name] = $state;
			$this->eventNames->merge($state->allEvents());

		}

		function all() {
			return $this->stateNames;
		}

		function allEvents() {
			return $this->eventNames;
		}

		function first() {
			return $this->states[$this->stateNames->first];
		}

		function state($name, $callback = false) {

			$state = new State($name);
			if (!empty($callback)) {
				($callback->bindTo($state, $state))();
			}

			$this->add($name, $state);

		}

	}

?>