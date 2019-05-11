<?php

namespace Aqua;

	class SqlString extends Node {

		protected $_rawQuery = "";
		protected $_params = [];

		function __construct($rawQuery, $params = []) {

			parent::__construct();
			$this->_class = "SqlString";

			$this->_rawQuery = $rawQuery;

			if (!is_array($params)) {
				$params = [$params];
			}
			$this->_params = $params;

			$this->attributeReadOnly("rawQuery", "params");

		}

	}

?>