<?php

namespace Aqua;

	class Where {

		use Accessor;

		private $_root = null;
		private $_current = null;

		function __construct() {
			$this->attributeReadOnly("root");
		}

		function append(Node $clause) {

			$newNode = new AndExpr($clause, null);
			if (is_null($this->_root)) {
				$this->_root = $this->_current = $newNode;
			}
			else {

				$this->_current->right = $newNode;
				$this->_current = $newNode;

			}

			return $this;

		}

		function empty() {
			return is_null($this->_root);
		}

	}

?>