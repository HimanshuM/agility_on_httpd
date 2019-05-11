<?php

namespace Agility\Caching;

use Agility\Config;
use ArrayUtils\Arrays;

	final class Cache {

		protected $objects = [];
		public $ttl = 3600;

		private static $_instance;

		private function __construct() {
			Config::cache($this);
		}

		static function initialize() {

			if (empty(static::$_instance)) {
				static::$_instance = new Cache;
			}

		}

		static function get($key) {

			if (isset(static::$_instance->objects[$key])) {

				$object = static::$_instance->objects[$key];
				return $object->access();

			}

			return null;

		}

		static function has($key) {

			if (isset(static::$_instance->objects[$key])) {
				return static::$_instance->objects[$key];
			}

			return false;

		}

		static function push($key, $value) {

			static::touch($key);
			static::$_instance->objects[$key]->push($value);

		}

		static function runGC() {

			$collectible = [];
			foreach (static::$_instance->objects as $name => $value) {

				if ($value->destroy(Config::cache()->ttl)) {
					$collectible[] = $name;
				}

			}

			foreach ($collectible as $name) {
				unset(static::$_instance->objects[$name]);
			}

		}

		static function set($key, $value, $expiry = 0) {

			static::touch($key, $expiry);
			static::$_instance->objects[$key]->access($value);

		}

		static function touch($key, $expiry = -1) {

			if (static::get($key) === null) {
				static::$_instance->objects[$key] = new Node($key, "", $expiry = -1);
			}

		}

	}

?>