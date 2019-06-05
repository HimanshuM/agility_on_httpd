<?php

namespace Aqua\Visitors;

use Aqua\Accessor;
use Aqua\Attribute;
use Aqua\NamedFunction;
use Aqua\SqlString;
use Aqua\Statement;

	class AbstractVisitor {

		use Accessor;
		use AttributeParser;
		use Sanitizer;

		protected $_statement;

		protected $_query;
		protected $_params = [];

		protected $_class;

		protected $_limitAll = "ALL";

		function __construct() {

			$this->_class = get_called_class();
			$this->attributeReadOnly("query", "params");

		}

		function initialize(Statement $statement) {
			$this->_statement = $statement;
		}

		function compile(Statement $statement) {

			$this->initialize($statement);

			if (is_a($statement, "Aqua\\SelectStatement")) {
				$this->_compileSelect();
			}
			else if (is_a($statement, "Aqua\\InsertStatement")) {
				$this->_compileInsert();
			}
			else if (is_a($statement, "Aqua\\UpdateStatement")) {
				$this->_compileUpdate();
			}
			else if (is_a($statement, "Aqua\\DeleteStatement")) {
				$this->_compileDelete();
			}
			else if (is_a($statement, "Aqua\\DescribeStatement")) {
				$this->_compileDescribe();
			}

			$this->_query .= ";";

		}

		protected function _compileSelect() {

			$this->_query = "SELECT ".$this->_compileProjections()." FROM ".$this->sanitize($this->_statement->relation->_name);

			$joins = $this->_compileJoins();
			if (!empty($joins)) {

				$this->_query .= " ".$joins[0];
				$this->_addToParams($joins[1]);

			}

			$wheres = $this->_compileWhere();
			if (!empty($wheres)) {

				$this->_query .= " ".$wheres[0];
				$this->_addToParams($wheres[1]);

			}

			$groups = $this->_compileGroup();
			if (!empty($groups)) {
				$this->_query .= " ".$groups;
			}

			$orders = $this->_compileOrder();
			if (!empty($orders)) {
				$this->_query .= " ".$orders;
			}

			$limits = $this->_compileLimit();
			if (!empty($limits)) {
				$this->_query .= " ".$limits;
			}

		}

		protected function _compileInsert() {

			$this->_query = "INSERT INTO ".$this->sanitize($this->_statement->relation->_name);
			$this->_compileValues();

		}

		protected function _compileUpdate() {

			$this->_query = "UPDATE ".$this->_compileUpdateSources();

			$joins = $this->_compileJoins();
			if (!empty($joins)) {

				$this->_query .= " ".$joins[0];
				$this->_addToParams($joins[1]);

			}

			list($query, $params) = $this->_compileSets();
			$this->_query .= " ".$query;
			$this->_addToParams($params);

			$wheres = $this->_compileWhere();
			if (!empty($wheres)) {

				$this->_query .= " ".$wheres[0];
				$this->_addToParams($wheres[1]);

			}

			$orders = $this->_compileOrder();
			if (!empty($orders) && empty($joins)) {
				$this->_query .= " ".$orders;
			}

			$limits = $this->_compileLimit();
			if (!empty($limits) && empty($joins)) {
				$this->_query .= " ".$limits;
			}

		}

		protected function _compileDelete() {

			$this->_query = "DELETE ".$this->_compileDeleteSources()."FROM ".($this->sanitize($this->_statement->relation[0]->_name));

			$joins = $this->_compileJoins();
			if (!empty($joins)) {

				$this->_query .= " ".$joins[0];
				$this->_addToParams($joins[1]);

			}

			$wheres = $this->_compileWhere();
			if (!empty($wheres)) {

				$this->_query .= " ".$wheres[0];
				$this->_addToParams($wheres[1]);

			}

			$orders = $this->_compileOrder();
			if (!empty($orders) && empty($joins)) {
				$this->_query .= " ".$orders;
			}

			$limits = $this->_compileLimit();
			if (!empty($limits) && empty($joins)) {
				$this->_query .= " ".$limits;
			}

		}

		protected function _compileDescribe() {
			$this->_query = "DESCRIBE ".$this->sanitize($this->_statement->relation->_name);
		}

		protected function _compileProjections() {

			if (empty($this->_statement->projections)) {
				return "*";
			}

			$attributes = [];
			$projection;
			$projections = [];

			foreach ($this->_statement->projections as $attribute) {

				if (is_a($attribute, SqlString::class)) {

					if (strpos($attribute->rawQuery, ".") !== false) {

						$components = explode(".", $attribute->rawQuery);
						$projection = $this->sanitize($components[0]).".".($components[1] == "*" ? "*" : $this->sanitize($components[1]));

					}
					else {
						$projection = $this->sanitize($attribute->rawQuery);
					}

				}
				else if (is_a($attribute->name, NamedFunction::class)) {
					$projection = $this->_compileFunction($attribute->name);
				}
				else if (is_a($attribute, Attribute::class)) {
					$projection = $this->sanitize($this->getTableName($attribute->table)).".".$this->sanitize($attribute->name);
				}

				if (is_a($attribute, SqlString::class)) {
					$projections[] = $projection;
				}
				else if (!empty($attribute->as)) {

					$projection .= " AS ".$attribute->as;
					$projections[] = $attribute->as;

				}
				else {

					if (in_array($attribute->name, $projections)) {
						$projection .= " AS ".$this->getTableName($attribute->table)."_".$attribute->name;
					}
					else {
						$projections[] = $attribute->name;
					}

				}

				$attributes[] = $projection;

			}

			return implode(", ", $attributes);

		}

		protected function _compileJoins() {

			if (empty($this->_statement->joins)) {
				return;
			}

			$query = "";
			$params = [];
			foreach ($this->_statement->joins as $join) {

				$query .= $this->_class::Joins[$this->_getClass(get_class($join))]." ";

				if (is_a($join->with, "Aqua\\Table")) {
					$query .= $this->sanitize($join->with->_name);
				}
				else if (is_a($join->with, "Aqua\\Statement")) {

					$clone = clone $this;
					$join->with->toSql($clone);
					$query .= "(".substr($clone->query, 0, -1).")";

					$params = array_merge($params, $clone->params);

				}
				else {
					$query .= $this->sanitize($join->with);
				}

				if (!empty($join->as)) {
					$query .= " AS ".$this->sanitize($join->as);
				}

				if (!empty($join->on)) {
					$query .= " ON ".$this->_compileNodes($join->on->expr->root)->nodeQuery;
				}

				$query .= " ";

			}

			return [trim($query), $params];

		}

		protected function _compileWhere() {

			if (empty($this->_statement->where->root)) {
				return;
			}

			$where = $this->_compileNodes($this->_statement->where->root);

			return ["WHERE ".$where->nodeQuery, $where->params];

		}

		protected function _compileGroup() {

			if (empty($this->_statement->groupings)) {
				return;
			}

			$query = [];
			foreach ($this->_statement->groupings as $group) {

				$clause = "";
				if (is_a($group, SqlString::class)) {

					if (strpos($group->rawQuery, ".") !== false) {

						$components = explode(".", $group->rawQuery);
						$clause = $this->sanitize($components[0]).".".$this->sanitize($components[1]);

					}
					else {
						$clause = $this->sanitize($group->rawQuery);
					}

				}
				else {
					$clause = $this->buildAttributeName($group);
				}

				$query[] = $clause;

			}

			return "GROUP BY ".implode(", ", $query);

		}

		protected function _compileOrder() {

			if (empty($this->_statement->order)) {
				return;
			}

			$query = [];
			foreach ($this->_statement->order as $order) {

				$clause = "";
				if (is_a($order->attribute, SqlString::class)) {

					if (strpos($order->attribute->rawQuery, ".") !== false) {

						$components = explode(".", $order->attribute->rawQuery);
						$clause = $this->sanitize($components[0]).".".$this->sanitize($components[1]);

					}
					else {
						$clause = $this->sanitize($order->attribute->rawQuery);
					}

				}
				else {
					$clause = $this->buildAttributeName($order->attribute);
				}

				$clause .= " ".($order->sequence == 1 ? "ASC" : "DESC");
				$query[] = $clause;

			}

			return "ORDER BY ".implode(", ", $query);

		}

		protected function _compileLimit() {

			if (empty($this->_statement->limit)) {
				return;
			}

			$length = $this->_statement->limit->length;
			$query = "LIMIT ".($length == -1 ? $this->_limitAll : $length);
			if ($this->_statement->limit->offset > 0) {
				$query .= " OFFSET ".$this->_statement->limit->offset;
			}

			return $query;

		}

		protected function _compileFunction(NamedFunction $function) {
			return get_called_class()::Functions[$function->function]."(".$this->sanitize($this->getTableName($function->attribute->table)).".".$this->sanitize($function->attribute->name).")";
		}

		protected function _compileNodes($nodeTree) {
			return (new NodeCompiler($nodeTree, $this->_class::Operators, clone $this))->compile();
		}

		protected function _addToParams($params) {
			$this->_params = array_merge($this->_params, $params);
		}

		protected function _getClass($class) {

			$components = explode("\\", $class);
			return array_pop($components);

		}

		protected function _compileValues() {

			if (!empty($this->_statement->values)) {
				$this->_compileColumnInserts();
			}
			elseif (!empty($this->_statement->source)) {
				$this->_compileSelectInserts();
			}
			else {
				throw new Exceptions\EmptyInsertValuesException($this->sanitize($this->_statement->relation->_name));
			}

		}

		protected function _compileColumnInserts() {

			$projections = [];
			$values = [];
			foreach ($this->_statement->values as $attribute) {

				$projections[] = $this->sanitize($attribute[0]->name);
				if (is_a($attribute[1], SqlString::class)) {
					$values[] = $attribute[1]->rawQuery;
				}
				else {

					$this->_params[] = $attribute[1];
					$values[] = "?";

				}

			}

			$this->_query .= "(".implode(", ", $projections).") VALUES (".implode(", ", $values).")";

		}

		protected function _compileSelectInserts() {

			$projections = [];
			foreach ($this->_statement->values as $attribute) {
				$projections[] = $this->sanitize($attribute->name);
			}

			if (!empty($projections)) {
				$this->_query .= "(".implode(", ", $projections).") ";
			}

			$visitor = clone $this;
			$this->_statement->source->toSql($visitor);
			$this->_query .= substr($visitor->sql, 0, -1);

		}

		protected function _compileUpdateSources() {

			$sources = [];
			foreach ($this->_statement->relation as $relation) {
				$sources[] = $this->sanitize($relation->_name);
			}

			return implode(", ", $sources);

		}

		protected function _compileSets() {

			if (empty($this->_statement->updates)) {
				throw new Exception("Assignment list cannot be empty in Update statement", 1);
			}

			$sets = [];
			$params = [];
			foreach ($this->_statement->updates as $update) {

				$lhs = $update[0];
				$query = $this->sanitize($this->getTableName($lhs->table)).".".$this->sanitize($lhs->name)." = ";

				$rhs = $update[1];
				if (is_a($rhs, "Aqua\\Attribute")) {
					$query .= $this->sanitize($this->getTableName($rhs->table)).".".$this->sanitize($rhs->name);
				}
				elseif (is_a($rhs, SqlString::class)) {
					$query .= $rhs->rawQuery;
				}
				else {

					$query .= "?";
					$params[] = $rhs;

				}

				$sets[] = $query;

			}

			return ["SET ".implode(", ", $sets), $params];

		}

		protected function _compileDeleteSources() {

			if (count($this->_statement->relation) == 1 && empty($this->_statement->joins)) {
				return "";
			}

			$sources = [];
			foreach ($this->_statement->relation as $relation) {
				$sources[] = $this->sanitize($relation->_name);
			}

			return implode(", ", $sources)." ";

		}

	}

?>