<?php

namespace Agility\Data\Migration;

use Agility\Data\Connection;
use Agility\Data\Connection\Pool;
use Agility\Data\Schema\Attribute;
use Agility\Data\Schema\ForeignKeyRelation;
use Agility\Data\Schema\Table;
use Agility\Data\Types\Base AS TypesBase;
use ArrayUtils\Arrays;
use Exception;
use Phpm\Exceptions\MethodExceptions\InsufficientParametersException;
use StringHelpers\Inflect;

	abstract class Base {

		protected $connectionName = 0;
		protected $connection;

		protected $table;
		protected $indices = [];
		protected $removedIndices = [];
		protected $foreignKeys = [];

		protected $newColumns = [];
		protected $changedColumns = [];
		protected $removeColumns = [];

		function __construct() {

		}

		protected function addColumn($tableName, $attrName, $dataType, $options = []) {

			$dataType = TypesBase::getType($dataType);
			// Send options to the data type for consumption of limit, precision, size, foreignKey or polymorphic, etc
			$dataType->setParameters($options);

			$attribute = Attribute::buildFromOptions($attrName, $dataType, $options);

			if (!isset($this->newColumns[$tableName])) {
				$this->newColumns[$tableName] = [];
			}

			$this->newColumns[$tableName][$attrName] = $attribute;

		}

		protected function addForeignKey($referencingTable, $sourceTable, $options = []) {
			$this->foreignKeys[] = ForeignKeyRelation::build($referencingTable, $sourceTable, $options);
		}

		protected function addIndex($tableName, $attrName, $unique = false) {

			if (!isset($this->indices[$tableName])) {
				$this->indices[$tableName] = [];
			}

			if (is_array($attrName)) {
				$attrName = implode("#", $attrName);
			}

			$this->indices[$tableName][$attrName] = $unique ? "unique" : "index";

		}

		abstract function change();

		// Datatype is an optional key in options hash
		protected function changeColumn($tableName, $attrName, $options = []) {

		}

		protected function createTable() {

			$args = func_get_args();
			if (count($args) == 0) {
				throw new InsufficientParametersException("Agility\\Data\\Migration\\Base::createTable", 1, 0);
			}

			$tableName = false;
			$primaryKey = null;
			$callback = null;
			$options = [];

			foreach ($args as $arg) {

				if (is_string($arg)) {

					if (empty($tableName)) {
						$tableName = $arg;
					}
					else if (empty($primaryKey)) {
						$primaryKey = $arg;
					}

				}
				else if (is_bool($arg) && empty($primaryKey)) {
					$primaryKey = $arg;
				}
				else if (is_callable($arg) && empty($callback)) {
					$callback = $arg;
				}
				else {
					$options[] = $arg;
				}

			}

			if (empty($tableName)) {
				throw new Exception("Table name not specified in Agility\\Data\\Migration\\Base::createTable()", 1);
			}
			if ($primaryKey === null) {
				$primaryKey = true;
			}

			$table = new Table($tableName, $primaryKey, $options);
			if (is_callable($callback)) {
				$callback($table);
			}

			$this->table = $table;

		}

		protected function prepareConnection() {

			if (empty($this->connection)) {
				$this->connection = Pool::getConnection($this->connectionName);
			}

		}

		private function processIndices() {

			foreach ($this->indices as $table => $indices) {

				foreach ($indices as $column => $index) {

					if (strpos($column, "#") !== false) {

						$column = explode("#", $column);
						$column = array_map(function($e) {
							return "`".$e."`";
						}, $column);
						$column = implode(", ", $column);

						$column = trim($column, "`");

					}

					$sql = "ALTER TABLE `".$table."` ADD ".strtoupper($index)." (`".$column."`);";
					$this->connection->execute($sql, [], Connection\Base::DDLStatement);

				}

			}

		}

		private function processForeignKeys() {

			foreach ($this->foreignKeys as $key) {

				$sql = $key->toSql($this->connection);
				$this->connection->execute($sql, [], Connection\Base::DDLStatement);

			}

		}

		function processMigration() {

			if (!Pool::initialized()) {
				throw new Exception("Migrations cannot be invoked directly. Please use console command 'agility db:migrate' to execute migrations");
			}

			$this->change();

			$this->prepareConnection();

			$this->processTable();
			$this->processNewColumns();
			$this->processIndices();
			$this->processForeignKeys();
			$this->processRemoval();

		}

		private function processNewColumns() {

			foreach ($this->newColumns as $table => $columns) {

				$sql = "ALTER TABLE `".$table."` ";
				$columnsSql = [];

				foreach ($columns as $column) {
					$columnsSql[] = "ADD ".$column->toSql($this->connection);
				}

				$sql .= implode(", ", $columnsSql).";";
				$this->connection->execute($sql, [], Connection\Base::DDLStatement);

			}

		}

		private function processRemoval() {

			foreach ($this->removeColumns as $table => $columns) {

				foreach ($columns as $column) {

					$sql = "ALTER TABLE `".$table."` DROP `".$column."`;";
					$this->connection->execute($sql, [], Connection\Base::DDLStatement);

				}

			}

		}

		private function processTable() {

			if (empty($this->table)) {
				return;
			}

			$sql = $this->table->toSql($this->connection);

			$this->connection->execute($sql, [], Connection\Base::DDLStatement);

			foreach ($this->table->indexed as $index) {
				$this->addIndex($this->table->name, $index);
			}

			foreach ($this->table->unique as $unique) {
				$this->addIndex($this->table->name, $unique, true);
			}

			foreach ($this->table->foreignKey as $key) {
				$this->foreignKeys[] = $key;
			}

		}

		protected function removeColumn($tableName, $attrName) {

			if (!isset($this->removeColumns[$tableName])) {
				$this->removeColumns[$tableName] = [];
			}

			$this->removeColumns[$tableName][] = $attrName;

		}

		protected function removeIndex($tableName, $attrName) {

			if (!isset($this->removedIndices[$tableName])) {
				$this->removedIndices[$tableName] = [];
			}

			$this->removedIndices[$tableName][] = $attrName;

		}

		protected function setConnection($connectionName) {
			$this->connectionName = $connectionName;
		}

	}

?>