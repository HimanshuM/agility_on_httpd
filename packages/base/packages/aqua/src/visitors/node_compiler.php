<?php

namespace Aqua\Visitors;

use Aqua\Accessor;
use Aqua\Node;
use Aqua\Where;

	class NodeCompiler {

		use Accessor;
		use AttributeParser;
		use Sanitizer;

		protected $_nodeTree;
		protected $_operatorsAssoc;

		protected $_visitor;

		protected $_nodeQuery = "";
		protected $_params = [];

		function __construct(Node $node, $operatorsAssoc, $visitor) {

			$this->_nodeTree = $node;
			$this->_operatorsAssoc = $operatorsAssoc;

			$this->_visitor = $visitor;

			$this->attributeReadOnly("nodeQuery", "params");

		}

		function compile() {

			$this->_nodeQuery = $this->_traverseTree($this->_nodeTree);

			return $this;

		}

		protected function _traverseTree(Node $node) {
			return $this->_leftChild($node).$this->_rightChild($node);
		}

		protected function _leftChild(Node $node) {
			return $this->_compileNode($node->left);
		}

		protected function _rightChild(Node $node) {

			if (is_null($node->right)) {
				return "";
			}

			return " ".$this->_operatorsAssoc[$node->class]." ".$this->_compileNode($node->right);

		}

		protected function _compileNode(Node $node) {

			$query = "";
			if (get_class($node) == "Aqua\\Binary") {

				$query = $this->buildAttributeName($node->left)." ".$this->_operatorsAssoc[$node->class]." ";
				if (is_a($node->right, "Aqua\\Attribute")) {
					$query .= $this->buildAttributeName($node->right);
				}
				else if (is_null($node->right)) {
					$query .= "NULL";
				}
				else if ($node->class == "In" || $node->class == "NotIn") {

					$rhs = trim(str_repeat("?, ", count($node->right)), ", ");
					$query .= "($rhs)";
					$this->_params = array_merge($this->_params, $node->right);

				}
				else {

					$query .= "?";
					$this->_params[/*$this->_buildAttributeValue($node->left)*/] = $node->right;

				}

			}
			else if ($node->class == "Grouping") {
				$query = "(".$this->_traverseTree($node->expr).")";
			}
			else if ($node->class == "And" || $node->class == "Or") {
				$query = $this->_traverseTree($node);
			}
			else if ($node->class == "Exists") {

				$node->left->toSql($this->_visitor);
				$query = (!$node->right ? "NOT " : "")."EXISTS (".substr($this->_visitor->query, 0, -1).")";
				$this->_params = array_merge($this->_params, $this->_visitor->params);

			}
			else if ($node->class == "SqlString") {

				$query = "(".$node->rawQuery.")";
				if (!empty($node->params)) {
					$this->_params = array_merge($this->_params, $node->params);
				}

			}

			return $query;

		}

	}

?>