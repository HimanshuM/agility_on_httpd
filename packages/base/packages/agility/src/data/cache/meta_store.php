<?php

namespace Agility\Data\Cache;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	final class MetaStore {

		use Accessor;

		private $aquaTables;
		private $connections;
		private $tableNames;
		private $generatedAttributes;
		private $scope;
		private $modelInitialized;

		private static $_instance;

		private function __construct() {

			$this->aquaTables = new Arrays;
			$this->connections = new Arrays;
			$this->tableNames = new Arrays;
			$this->generatedAttributes = new Arrays;
			$this->scope = new Arrays;
			$this->modelInitialized = new Arrays;

			$this->readonly("aquaTables", "connections", "tableNames", "generatedAttributes", "modelInitialized", "scope");

		}

		static function instance() {

			if (empty(static::$_instance)) {
				static::$_instance = new MetaStore;
			}

			return static::$_instance;

		}

	}

?>