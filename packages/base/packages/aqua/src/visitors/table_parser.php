<?php

namespace Aqua\Visitors;

	trait TableParser {

		function getTableName($table) {

			if (is_a($table, "Aqua\\Table")) {
				return (empty($table->_alias) ? $table->_name : $table->_alias);
			}

			return $table;

		}

	}

?>