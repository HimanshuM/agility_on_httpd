<?php

namespace Agility\Data\Relations;

use Agility\Data\Relation;

	trait QueryExtensions {

		protected static function initializeScope() {

			if (!static::metaStore()->scope) {
				static::metaStore()->scope = new Scope(Relation::class, static::class, static::class);
			}

		}

		static function getOrAddScope() {

			static::initializeScope();
			return static::getScope();

		}

		protected static function getScope() {
			return static::metaStore()->scope;
		}

		protected static function hasScope($name) {

			static::staticInitialize();
			static::initializeScope();

			return static::getScope()->has($name);

		}

		protected static function scope($name, $callback = null) {

			static::initializeScope();
			static::getScope()->add($name, $callback);

		}

		protected static function tryScope($name, $args = []) {

			if (static::metaStore()->scope->has($name)) {

				static::metaStore()->scope->restart();
				return static::getScope()->$name($args);

			}

		}

	}

?>