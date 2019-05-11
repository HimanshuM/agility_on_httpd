<?php

namespace Aqua;

use Exception;

	class Order {

		use Accessor;

		protected $_attribute;
		protected $_sequence;

		function __construct($attribute, int $sequence) {

			if (is_string($attribute)) {
				$attribute = new SqlString($attribute);
			}
			else if (!is_a($attribute, Attribute::class)) {
				throw new Exception("Order by attribute should either be a string or an instance of class Aqua\\Attribute", 1);
			}

			$this->_attribute = $attribute;
			$this->_sequence = $sequence;

			$this->attributeReadOnly("attribute", "sequence");

		}

	}

?>