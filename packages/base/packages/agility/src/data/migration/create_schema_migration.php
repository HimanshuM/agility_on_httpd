<?php

namespace Agility\Data\Migration;

	class CreateSchemaMigration extends Base {

		function change() {

			$this->prepareConnection();
			$this->createTable($this->tableName(), false, function ($t) {

				$t->string("version", ["null" => false]);
				$t->primaryKey("version");

			});

		}

		function tableName() {
			return ($this->connection->prefix)."schema_migrations".($this->connection->suffix);
		}

	}

?>