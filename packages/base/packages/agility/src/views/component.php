<?php

namespace Agility\Views;

	class Component {

		public $templateUrl = "";
		public $template = "";
		public $styleSheets = [];
		public $selector = "ag-view";
		public $element = false;
		public $includes = [];
		public $parent = false;

		function __construct($element = false, $parent = false) {

			$this->element = $element;
			$this->parent = $parent;

		}

		protected function init($instance, $callback, $parent) {

			$this->instance = $instance;
			$this->callback = $callback;
			$this->parent = $parent;
			$this->fetch();

		}

		protected function onInit() {

		}

		protected function include(...$args) {
			$this->includes = $args;
		}

	}

?>