<?php

namespace Aqua;

	class Limit {

		use Accessor;

		protected $_offset;
		protected $_length;

		function __construct(int $offset = 0, int $length = -1) {

			$this->_offset = $offset;
			$this->_length = $length;

			$this->attributeReadOnly("offset", "length");

		}

	}

?>