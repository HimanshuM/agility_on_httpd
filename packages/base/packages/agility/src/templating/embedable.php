<?php

namespace Agility\Templating;

use Agility\Configuration;
use ArrayUtils\Arrays;

	trait Embedable {

		use HtmlTags;

		protected $cssCache;
		protected $jsCache;
		protected $title;

		protected $content = [];

		function contentFor($name, $callback = null) {

			if (!empty($callback)) {
				return $this->content[$name] = $callback;
			}

			if (empty($this->content[$name])) {
				return "";
			}

			$callback = $this->content[$name];
			return $callback();

		}

		function css() {

			$args = func_get_args();
			if (count($args) == 0) {
				return $this->echoCss();
			}

			foreach ($args as $arg) {
				$this->cssCache[] = $arg;
			}

			if (empty($this->layout)) {
				return $this->echoCss();
			}

		}

		protected function cssLink($css) {

			if (is_array($css)) {

				if (isset($css["style"])) {
					return $this->tag("style", [], false, $css["style"]);
				}
				else if (isset($css["src"])) {
					return $this->tag("link", ["rel" => "stylesheet", "href" => $this->getEmbedablePath($css)]);
				}

			}

			$url = parse_url($css);
			if (empty($url) || !isset($url["host"])) {
				return $this->tag("link", ["rel" => "stylesheet", "href" => $this->getEmbedablePath($css)]);
			}
			else {
				return $this->tag("link", ["rel" => "stylesheet", "href" =>$css]);
			}

		}

		protected function echoCss() {

			foreach ($this->cssCache as $css) {
				echo $this->cssLink($css);
			}

		}

		protected function echoJs() {

			foreach ($this->jsCache as $js) {
				echo $this->jsLink($js);
			}

		}

		protected function getEmbedablePath($resource, $css = true) {

			if ($css) {
				return Configuration::cssPath().$resource.".css";
			}
			else {
				return Configuration::jsPath().$resource.".js";
			}

		}

		function js() {

			$args = func_get_args();
			if (count($args) == 0) {
				return $this->echoJs();
			}

			foreach ($args as $arg) {
				$this->jsCache[] = $arg;
			}

			if (empty($this->layout)) {
				return $this->echoJs();
			}

		}

		protected function jsLink($js) {

			if (is_array($js)) {

				$attribute = [];
				if (isset($js["src"])) {

					$attribute["type"] = "text/javascript";
					$attribute["src"] = $js["src"];

				}

				return $this->tag("script", $attribute, false, $js["script"] ?? "");

			}

			$url = parse_url($js);
			if (empty($url) || !isset($url["host"])) {
				return $this->tag("script", ["src" => $this->getEmbedablePath($js, false)]);
			}
			else {
				return $this->tag("script", ["src" => $js]);
			}

		}

		function title($title = null) {

			if (is_string($title)) {
				$this->title = $title;
			}

			return $this->title;

		}

	}

?>