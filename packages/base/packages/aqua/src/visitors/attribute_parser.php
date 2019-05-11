<?php

namespace Aqua\Visitors;

	trait AttributeParser {

		use TableParser;

		protected function buildAttributeName($attribute) {
			return $this->sanitize($this->getTableName($attribute->table)).".".$this->sanitize($attribute->name);
		}

		protected function buildAttributeValue($attribute) {
			return ":".$this->getTableName($attribute->table)."_".$attribute->name;
		}
	}

?>