<?php

namespace Aqua;

use Exception;

	abstract class Join {

		use Accessor;

		protected $_with;
		protected $_on;
		protected $_as;

		function __construct($with, $on = null) {

			if (empty($with)) {
				throw new Exception("Cannot build join with empty table", 1);
			}

			$this->_with = $with;

			if (!empty($on)) {

				if (!($null instanceof On)) {
					throw new Exception("Invalid on clause specified. It should be an object of class Aqua\\On", 1);
				}

				$this->_on = $on;

			}

			$this->attributeReadOnly("with");
			$this->attributeAccessible("on", "as");

		}

	}

?>