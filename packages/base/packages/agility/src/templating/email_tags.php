<?php

namespace Agility\Templating;

	trait EmailTags {

		function embedImg($src, $width = -1, $height = -1, $options = []) {

			$attachment = $this->attachments($src);
			$src = $attachment->url();
			$options["alt"] = $attachment->name;

			return $this->img($src, $options);

		}

	}

?>