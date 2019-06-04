<?php

namespace Agility\Server;

use Agility\Templating\Template;
use Agility\Templating\ViewNotFoundException;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use Closure;
use MethodTriggers\Trigger;
use StringHelpers\Str;

	abstract class AbstractController {

		use Accessor;
		use Trigger;

		protected $params;

		protected $_storableName = false;
		protected $methodInvoked;
		protected $_responded = false;
		protected $_content = false;

		function __construct() {

			$this->params = new Parameter;

			$this->notFoundResponse(ACCESSOR_NOT_FOUND_ALLOW);
			$this->readonly("params");

		}

		static function __callStatic($name, $args) {
			return static::invoke($name, $args);
		}

		abstract protected function conclude($return);

		// What to do if a before trigger already responded
		protected function concludeOnRespondedByBeforeTrigger() {
			return true;
		}

		function execute($method) {

			$return = null;
			if (is_a($method, Closure::class)) {
				$return = ($method->bindTo($this, $this))();
			}
			else {

				$this->methodInvoked = $method;
				$this->invokeTriggerFor($method);

				if ($this->_responded && $this->concludeOnRespondedByBeforeTrigger()) {
					return $this->conclude([]);
				}

				$return = $this->$method();

				$this->invokeTriggerFor($method, false);

			}

			return $this->conclude($return);

		}

		protected function getRelativeClassName() {
			return $this->_storableName = $this->_storableName ?: Str::storable(str_replace(["App\\Controllers\\", "Controller"], "", get_called_class()));
		}

		static function invoke($method, $args) {

			$instance = new static;
			$instance->prepareParams($args);
			$return = $instance->execute($method);

			// Return the instance if the subclass has not returned anything
			if (empty($return)) {
				$return = $instance;
			}

			return $return;

		}

		protected function prepareParams($args) {

			if (!is_a($args, Arrays::class)) {
				$args = new Arrays($args);
			}

			$this->params = $args;

		}

	}

?>