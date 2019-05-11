<?php

namespace Aqua;

	class NamedFunction {

		use Accessor;

		protected $_attribute;
		protected $_function;

		public static $id = 0;

		function __construct($function, $attribute) {

			$this->_function = $function;
			$this->_attribute = $attribute;

			$this->attributeReadOnly("attribute", "function");

			NamedFunction::$id++;

		}

	}

?>