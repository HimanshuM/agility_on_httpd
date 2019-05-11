<?php

namespace Aqua;

	abstract class Node {

		protected $_class = "Node";

		use Accessor;

		function __construct() {
			$this->attributeReadOnly("class");
		}

		function and(Node $expression) {
			return new AndExpr($this, $expression);
		}

		function or(Node $expression) {
			return new Grouping(new OrExpr($this, $expression));
		}

		static function build($expr, $params = []) {

			if (is_string($expr)) {
				return new SqlString($expr, $params);
			}
			else if (is_a($expr, "Aqua\\Node")) {
				return $expr;
			}
			else {
				throw new Exception("Invalid type passed for Node", 1);
			}

		}

	}

?>