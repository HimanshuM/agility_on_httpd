<?php

namespace Agility\Data\Helpers;

use Agility\Data\Metadata\MetaStore;
use Agility\Data\Relation;
use Agility\Data\Collection;
use Agility\Data\Connection;
use Agility\Data\Validations\ValidationErrors;
use Agility\Data\Validations\Validations;
use Aqua\Table;
use ArrayUtils\Arrays;

	trait Initializer {

		protected static $_metaStore;

		static function aquaTable() {

			if (!static::metaStore()->aquaTable) {
				static::metaStore()->aquaTable = new Table(static::connection()->prefix.static::tableName().static::connection()->suffix);
			}

			return static::metaStore()->aquaTable;

		}

		static function connection() {

			if (!static::metaStore()->connection) {

				if (empty(static::$connection)) {
					static::metaStore()->connection = Connection\Pool::getConnection();
				}
				else {
					static::metaStore()->connection = Connection\Pool::getConnection(static::$connection);
				}

			}

			return static::metaStore()->connection;

		}

		protected function _initialize() {

			static::staticInitialize();

			$this->attributes = new Collection(static::class);
			$this->_backups = new Arrays;
			$this->_fresh = true;
			$this->_dirty = false;
			$this->_persisted = false;

			$this->errors = new ValidationErrors;

			$this->methodsAsProperties();
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "defaultCallback");
			$this->issetOverride("isSet");

			static::generateAttributes();

			$this->_runCallbacks("afterInitialize");

		}

		protected static function initializeRelation() {

			static::staticInitialize();
			return (new Relation(static::class));

		}

		protected static function metaStore() {

			if (empty(static::$_metaStore)) {
				static::$_metaStore = new Arrays;
			}

			if (!static::$_metaStore->exists(static::class)) {
				static::$_metaStore[static::class] = new MetaStore;
			}

			return static::$_metaStore[static::class];

		}

		static function staticInitialize() {

			if (static::metaStore()->modelInitialized) {
				return;
			}

			static::metaStore()->modelInitialized = true;

			static::metaStore();

			static::connection();
			static::tableName();
			static::aquaTable();

			// static::generateAttributes();
			static::initializeAssociations();

			// if (!static::metaStore()->modelInitialized) {

				if (method_exists(static::class, "initialize")) {
					static::initialize();
				}
				// static::metaStore()->modelInitialized = true;

			// }

		}

		static function tableName() {

			if (!static::metaStore()->tableName) {

				static::connection();

				$tableName;
				if (!empty(static::$tableName)) {
					$tableName = static::$tableName;
				}
				else {
					$tableName = NameHelper::tablize(static::class);
				}

				$tableName = (static::metaStore()->connection->tablePrefix).$tableName.(static::metaStore()->connection->tableSuffix);

				static::metaStore()->tableName = $tableName;
				static::aquaTable();

			}

			return static::metaStore()->tableName;

		}

	}

?>