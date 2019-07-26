<?php

namespace Workflow;

use ArrayUtils\Arrays;

	trait Workflow {

		static protected $workflows;

		protected static function _staticInitializeWorkflow() {

			if (empty(static::$workflows)) {
				static::$workflows = new Arrays;
			}

			if (!static::$workflows->exists(static::class)) {
				static::$workflows[static::class] = new States;
			}

			return static::$workflows[static::class];

		}

		protected function _initializeWorkflow() {
			$this->workflowState = static::_workflow()->first->name;
		}

		protected function invokeWorkflowEvent($name, $args) {

			if (static::_workflow()->states->exists($name)) {

				$args->handled();
				return $this->workflowState == $name;

			}

			if (($event = $this->_validEvent($name)) !== false) {

				$args->handled();
				return $event->invoke($this);

			}

			if (static::_workflow()->allEvents->has($name)) {

				$args->handled();
				throw new Exceptions\InvalidEventException($name, $this->workflowState, static::class);

			}

		}

		static function workflow($callback) {

			$workflow = static::_workflow();
			($callback->bindTo($workflow, $workflow))();

			$tableName = static::tableName();

			foreach (static::_workflow()->states->keys as $key) {

				static::scope($key, function() use ($tableName, $key) {
					$this->where($tableName.".workflow_state = ?", [$key]);
				});

			}

			static::validates("workflowState", "inclusion", ["in" => static::_workflow()->states->keys]);
			static::afterInitialize("_initializeWorkflow");

			static::registerFallbackCallable("invokeWorkflowEvent");

		}

		static function _workflow() {
			return static::_staticInitializeWorkflow();
		}

		protected function _validEvent($name) {

			if (static::_workflow()->states[$this->workflowState]->events->exists($name)) {
				return static::_workflow()->states[$this->workflowState]->events[$name];
			}

			return false;

		}

		function workflowStates() {
			return static::_workflow();
		}

	}

?>