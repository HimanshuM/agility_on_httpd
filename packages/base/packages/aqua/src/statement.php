<?php

namespace Aqua;

use Exception;

	class Statement {

		use Accessor;

		protected $_relation;
		protected $_where;

		function __construct(Table $table = null) {

			if (!empty($table)) {
				$this->_relation = $table;
			}

			$this->_where = new Where;

			$this->attributeReadOnly("relation", "where");

		}

		function toSql(Visitors\AbstractVisitor $visitor) {
			return $visitor->compile($this);
		}

		function where($clause, $params = []) {

			if (!is_string($clause) && !is_a($clause, Node::class)) {
				throw new Exception("Where clause should either be a string or an instance of class Aqua\\Node", 1);
			}

			$this->_where->append(Node::build($clause, $params));
			return $this;

		}

	}

?>