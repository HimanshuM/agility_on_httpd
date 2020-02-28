<?php

namespace Agility\Data\Connection;

use Agility\Configuration;
use Agility\Data\Collection;
use ArrayUtils\Arrays;
use FileSystem\File;

	final class Pool {

		static $defaultConnection = 0;
		static $pool;
		static $defaultPoolSize = 100;

		protected static $instanceType;
		protected static $isInitialized = false;

		static function getConnection($connectionName = null) {

			Pool::initialize();

			if (empty($connectionName)) {
				$connectionName = Pool::$defaultConnection;
			}

			if (!Pool::$pool->exists($connectionName)) {
				throw new ConnectionNotFoundException($connectionName);
			}

			return Pool::$pool[$connectionName];

		}

		static function initialize() {

			if (Pool::isInitialized()) {
				return;
			}

			if (empty(Configuration::logDbQueries())) {
				Configuration::logDbQueries(true);
			}

			Pool::$pool = new Arrays;
			Pool::$instanceType = Collection::class;

			return Pool::readConfigurationFile();

		}

		static function initialized() {
			return Pool::$isInitialized;
		}

		static function parseConfiguration($configuration) {

			$configuration = Configuration::dbConfiguration(json_decode($configuration, true))[Configuration::environment()];
			foreach ($configuration as $connectionName => $connectionArray) {

				Pool::$pool[$connectionName] = false;
				if (is_null($connectionObject = Factory::attemptConnection($connectionArray, Pool::$instanceType, Pool::$defaultPoolSize))) {
					throw new SqlConnectionFailedException("Could not connect to database '".$connectionArray["database"]."'");
				}

				Pool::$pool[$connectionName] = $connectionObject;

			}

			Pool::$isInitialized = true;

		}

		static function readConfigurationFile() {

			if (($file = Configuration::documentRoot()->has("config/database.json")) === false) {
				return false;
			}

			Pool::parseConfiguration(File::open($file)->read());

		}

	}

?>