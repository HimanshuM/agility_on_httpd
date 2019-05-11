<?php

namespace Agility\Templating;

use Agility\Configuration;
use ArrayUtils\Arrays;

	trait Render {

		use Embedable;

		protected $layout = "layout/base";
		protected $templateBase;
		protected $template;
		protected $_subContent = "";
		protected $count = 0;

		function content() {
			return $this->subContent;
		}

		protected function initializeTemplating() {

			$this->templateBase = "app/views/";
			$this->template = new Template(Configuration::documentRoot()->find($this->templateBase)->first, $this);

			$this->cssCache = new Arrays;
			$this->jsCache = new Arrays;

		}

		// Accepts an optional template name and an array
		// Possible keys of the array: "json", "view", "status", "data"
		function render() {

			$template = null;
			$partial = false;
			$templateTried = "NA";
			$options = [];
			$data = null;
			$status = 200;

			$args = func_get_args();
			foreach ($args as $arg) {

				if (is_string($arg) && empty($template)) {
					$template = $arg;
				}
				else if (is_array($arg) || is_a($arg, Arrays::class)) {
					$options = $arg;
				}
				else if (is_object($arg)) {
					$data = $arg;
				}

			}

			if (isset($options["json"])) {
				return $this->json($options["json"], true);
			}

			if (is_null($template) && empty($options["partial"]) && empty($options["view"])) {
				$template = $this->getRelativeClassName()."/".$this->methodInvoked;
			}

			if (!empty($template) || !empty($options["view"])) {

				$templateTried = $options["view"] ?? $template;
				$template = $this->template->templateExists($templateTried);
				$data = $data ?? $options["data"] ?? [];

			}
			else if (!empty($options["partial"])) {

				$templateTried = $options["partial"];
				$partial = true;
				$template = $this->template->templateExists($templateTried) ?: $this->template->templateExists($this->getRelativeClassName()."/".$templateTried) ?: false;
				$data = $data ?? $options["local"] ?? [];

			}

			if (empty($template)) {

				if (!empty($options["noError"])) {
					return "";
				}

				throw new ViewNotFoundException($templateTried, $partial);

			}

			$data = $this->template->load($template, $data);
			if (!$partial) {

				$this->_content = $this->renderBase($data);
				$this->_status = $options["status"] ?? $this->_status;

			}

			return $data;

		}

		function renderBase($data) {

			if (empty($this->layout)) {
				return $data;
			}

			$this->subContent = $data;

			return $this->template->load($this->layout);

		}

	}

?>