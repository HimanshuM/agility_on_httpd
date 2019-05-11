<?php

namespace Agility\Data\Schema;

use StringHelpers\Inflect;

	class ForeignKeyRelation {

		public $keyName;
		public $referencingColumnName;
		public $referencingTableName;
		public $sourceTableName;
		public $sourceColumnName;

		public $onUpdate;
		public $onDelete;

		function __construct($keyName, $referencingTableName, $referencingColumnName, $sourceTableName, $sourceColumnName, $onUpdate = 0, $onDelete = 0) {

			$this->referencingColumnName = $referencingColumnName;
			$this->referencingTableName = $referencingTableName;
			$this->sourceTableName = $sourceTableName;
			$this->sourceColumnName = $sourceColumnName;

			$this->onUpdate = $onUpdate;
			$this->onDelete = $onDelete;

			if (!empty($keyName)) {
				$this->keyName = $keyName;
			}
			else {
				$this->keyName = $this->prepareKeyName();
			}

		}

		static function build($referencingTableName, $sourceTableName, $options = []) {

			$sourceColumnName = $options["primaryKey"] ?? "id";
			$referencingColumnName = $options["column"] ?? Inflect::singularize($sourceTableName)."_id";
			$keyName = $options["keyName"] ?? "";

			$onUpdate = $options["onUpdate"] ?? 1;
			$onDelete = $options["onDelete"] ?? 1;

			return new ForeignKeyRelation($keyName, $referencingTableName, $referencingColumnName, $sourceTableName, $sourceColumnName, $onUpdate, $onDelete);

		}

		function prepareKeyName() {
			return "fk_".$this->referencingTableName."_".$this->referencingColumnName;
		}

		function toSql($connection) {
			return $connection->getTypeMapper()->compileForeignKey($this);
		}

	}

?>