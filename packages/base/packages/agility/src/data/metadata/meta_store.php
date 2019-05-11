<?php

namespace Agility\Data\Metadata;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	final class MetaStore {

		use Accessor;

		public $aquaTable = false;
		public $connection = false;
		public $tableName = false;
		public $generatedAttributes;
		public $attributeObjects;
		public $accessibleAttributes;
		public $protectedAttributes;
		public $scope = false;
		public $modelInitialized = false;
		public $validations;

		function __construct() {

			// $this->aquaTables = new Arrays;
			// $this->connections = new Arrays;
			// $this->tableNames = new Arrays;
			$this->generatedAttributes = new Arrays;
			$this->attributeObjects = new Arrays;
			$this->accessibleAttributes = new Arrays;
			$this->protectedAttributes = new Arrays;
			$this->validations = new Arrays;
			// $this->scope = new Arrays;
			// $this->modelInitialized = new Arrays;

			// $this->readonly("aquaTables", "connections", "tableNames", "generatedAttributes", "modelInitialized", "scope");

		}

	}

?>