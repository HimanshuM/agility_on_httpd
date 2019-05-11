<?php

namespace Agility\Templating;

	trait HtmlTags {

		use Navigation;

		protected $noClosing = [
			"meta",
			"link"
		];

		function img($src, $width = -1, $height = -1, $options = []) {

			if (is_string($src)) {

				$options["src"] = $src;

				$fileName = Str::componentName($src, "/");
				$options["alt"] = Str::humanize($fileName);

			}
			else if (isset($src["base64"])) {

				$src = "data:".($src["data"] ?? "image/jpg").";";
				if (!empty($src["charset"])) {
					$src .= "charset=".$src["charset"].";";
				}

				$options["src"] = "base64 ".$src["base64"];

			}

			if ($width > -1) {
				$options["width"] = $width;
			}
			if ($height > -1) {
				$options["height"] = $height;
			}

			return $this->tagBuilder("img", $options);

		}

		function input($type = "text", $id = "", $value = "", $options = []) {

			$options["type"] = $type;
			$options["id"] = $id;
			$options["value"] = $value;

			return $this->tagBuilder("input", $options, $options["class"] ?? [], $options["data"] ?? [], true);

		}

		function tag($name, $options = [], $open = false, $content = "") {

			if ($name == "input") {
				return $this->input($options["type"] ?? "text", $options);
			}
			else if ($name == "img") {
				return $this->img($options);
			}

			return $this->tagBuilder($name, $options, $options["class"] ?? [], $options["data"] ?? [], $open, $content);

		}

		protected function tagBuilder($name, $attributes = [], $class = [], $data = [], $open = false, $content = "") {

			$tag = "<".$name;

			if (!empty($attributes)) {

				$attr = [];
				foreach ($attributes as $key => $value) {

					if (!in_array($key, ["class", "data"])) {
						$attr[] = "$key=\"$value\"";
					}

				}

				$tag .= " ".implode(" ", $attr);

			}

			if (!empty($class)) {
				$tag .= " class=\"".implode(" ", $class);
			}

			if (!empty($data)) {

				$dataAttr = [];
				foreach ($data as $key => $value) {
					$dataAttr[] = "data-$key=\"$value\"";
				}

				$tag .= " ".implode(" ", $dataAttr);

			}

			if ($open) {
				$tag .= "/>";
			}
			else if (in_array($name, $this->noClosing)) {
				$tag .= ">";
			}
			else {

				$tagContent = "";
				if (!empty($content)) {

					if (is_string($content)) {
						$tagContent = $content;
					}
					else if (is_callable($content)) {
						$tagContent = $content();
					}

					$tagContent = "\n".$tagContent."\n";

				}

				$tag .= ">$tagContent</".$name.">";

			}

			echo $tag."\n";

		}

	}

?>