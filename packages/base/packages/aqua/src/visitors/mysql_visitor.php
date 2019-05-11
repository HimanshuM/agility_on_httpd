<?php

namespace Aqua\Visitors;

use Aqua\Node;
use Aqua\Statement;

	class MysqlVisitor extends AbstractVisitor {

		use Sanitizer;
		protected $_limitAll = PHP_INT_MAX;

		const Operators = [
			"Equal" => "=",
			"NotEqual" => "!=",
			"GreaterThan" => ">",
			"GreaterThanEqual" => ">=",
			"LessThan" => "<",
			"LessThanEqual" => "<=",
			"In" => "IN",
			"NotIn" => "NOT IN",
			"Is" => "IS",
			"IsNot" => "IS NOT",
			"Like" => "LIKE",
			"And" => "AND",
			"Or" => "OR"
		];

		const Functions = [
			"Average" => "AVG",
			"Count" => "COUNT",
			"Sum" => "SUM",
			"Max" => "MAX",
			"Min" => "MIN"
		];

		const Joins = [
			"InnerJoin" => "INNER JOIN",
			"LeftJoin" => "LEFT JOIN",
			"RightJoin" => "RIGHT JOIN",
			"FullJoin" => "FULL OUTER JOIN"
		];

	}

?>