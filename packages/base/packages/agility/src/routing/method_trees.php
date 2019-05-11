<?php

namespace Agility\Routing;

use AttributeHelper\Accessor;

	class MethodTrees {

		use Accessor;

		protected $delete;
		protected $get;
		protected $head;
		protected $options;
		protected $patch;
		protected $post;
		protected $put;

		function __construct() {

			$this->delete = new Helpers\Ast;
			$this->get = new Helpers\Ast;
			$this->head = new Helpers\Ast;
			$this->options = new Helpers\Ast;
			$this->patch = new Helpers\Ast;
			$this->post = new Helpers\Ast;
			$this->put = new Helpers\Ast;

			$this->readonly("delete", "get", "head", "options", "patch", "post", "put");

		}

		function appendSubtree($route, $ast) {

			$this->delete->appendSubtree($route, $ast->delete);
			$this->get->appendSubtree($route, $ast->get);
			$this->head->appendSubtree($route, $ast->head);
			$this->patch->appendSubtree($route, $ast->patch);
			$this->post->appendSubtree($route, $ast->post);
			$this->put->appendSubtree($route, $ast->put);

		}

	}

?>