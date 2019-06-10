<?php

namespace Agility\Data\Callbacks;

use ArrayUtils\Arrays;
use Swoole;

	trait Callback {

		protected static $CALLBACKS = [
			"afterInitialize", "afterFind",
			"beforeValidation", "beforeValidationOnCreate", "beforeValidationOnUpdate",
			"afterValidation", "afterValidationOnCreate", "afterValidationOnUpdate",
			"beforeSave", "beforeCreate", "beforeUpdate",
			"afterCreate", "afterUpdate", "afterSave",
			"beforeDelete", "afterDelete"
		];

		protected static $_callbacks;

		protected static function _addToCallbacks($callback, $args) {

			if (count($args) == 0 || !in_array($callback, static::$CALLBACKS)) {
				return;
			}

			$args = $args[0];
			$args = new CallbackDefinition($args, is_array($args));

			if (is_null(static::_theseCallbacks()[$callback])) {
				static::_theseCallbacks()[$callback] = new Arrays($args);
			}
			else {
				static::_theseCallbacks()[$callback]->merge($args);
			}

		}

		protected function _runCallbacks($callback, $args = []) {

			if (static::_theseCallbacks()->exists($callback)) {

				foreach (static::_theseCallbacks()[$callback] as $callback) {

					if (is_array($callback->callback)) {

						if (!empty($args)) {
							$args = [$this, $args];
						}
						else {
							$args = $this;
						}

						call_user_func_array($callback->callback, $args);

					}
					else {

						$callback = $callback->callback;
						$this->$callback($args);

					}

				}

			}

		}

		protected static function _theseCallbacks() {

			if (empty(static::$_callbacks)) {
				static::$_callbacks = new Arrays;
			}
			if (empty(static::$_callbacks[static::class])) {
				static::$_callbacks[static::class] = new Arrays;
			}

			return static::$_callbacks[static::class];

		}

	}

?>