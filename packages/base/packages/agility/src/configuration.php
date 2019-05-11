<?php

namespace Agility;

use ArrayUtils\Arrays;

	final class Configuration {

		private $_documentRoot;
		private $_environment;

		private $_dbConfiguration;

		private $_settings;
		private $_listeners;

		private static $_instance = null;

		private function __construct($documentRoot, $environment) {

			$this->_documentRoot = $documentRoot;
			$this->_environment = $environment;

			$this->_settings = new Arrays(["apiOnly" => false]);
			$this->_listeners = new Arrays;

		}

		static function __callStatic($setting, $args = []) {

			if ($setting == "root") {
				return Configuration::documentRoot();
			}

			if ($setting == "dbConfiguration") {

				if (empty(static::$_instance->_dbConfiguration) && !empty($args)) {
					static::$_instance->_dbConfiguration = $args[0];
				}

				return static::$_instance->_dbConfiguration;

			}

			if (!empty($args)) {

				$new = static::$_instance->_set($setting, $args[0]);
				// $new would be true if the setting has been newly added;
				// in which case, if the creator adds a listener,
				// the listener has the right to modify any new value set to the setting.
				// Hence, the listener, if added any by the creator, must return
				// at least the same value if a change is not desired.
				if (isset($args[1])) {
					static::$_instance->_addListener($setting, $args[1], $new);
				}

			}
			else if (static::$_instance->_settings->exists($setting)) {
				return static::$_instance->_settings[$setting];
			}
			else {
				return null;
			}

		}

		private function _addListener($setting, $listener, $creator = false) {

			if (!isset($this->_listeners[$setting])) {
				$this->_listeners[$setting] = new Arrays;
			}

			$this->_listeners[$setting][] = [$listener, $creator];

		}

		static function all() {

			return static::initialized() ? new Arrays([
				"environment" => static::$_instance->_environment
			]) : new Arrays;

		}

		private function _broadcast($setting, $value) {

			if ($this->_listeners->exists($setting)) {

				foreach ($this->_listeners[$setting] as $listener) {

					$value = call_user_func_array($listener, [$setting, $value]);

					// If listener is the creator, we allow the listener to modify the new value
					if ($listener[1]) {
						$this->_settings[$setting] = $value;
					}

				}

			}

		}

		static function documentRoot() {
			return static::initialized() ? clone static::$_instance->_documentRoot : null;
		}

		static function environment($env = false) {

			if (!static::initialized()) {
				return null;
			}

			if (empty($env)) {
				return static::$_instance->_environment;
			}
			else {
				return strtolower($env) == static::$_instance->_environment;
			}

		}

		static function file($name) {
			return Configuration::$_instance->_documentRoot->has("config/".$name);
		}

		static function initialize($documentRoot = "public", $environment = "development") {

			if (is_null(static::$_instance)) {
				static::$_instance = new static($documentRoot, $environment);
			}

			return static::$_instance;

		}

		static function initialized() {
			return !is_null(static::$_instance);
		}

		private function _set($setting, $value) {

			$new = false;
			if (!$this->_settings->exists($setting)) {
				$new = true;
			}

			$this->_settings[$setting] = $value;
			$this->_broadcast($setting, $value);

			return $new;

		}

	}

	class_alias("Agility\Configuration", "Agility\Config");

?>