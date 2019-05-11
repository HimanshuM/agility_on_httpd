<?php

namespace Agility\Templating;

	trait EmailTags {

		function embedImg($src, $width = -1, $height = -1, $options = []) {

			$attachment = $this->options->addInlineAttachment($src, $options);
			$options["src"] = "cid:".$attachment->contentId;
			$options["alt"] = $attachment->name;

			return $this->img($src, $options);

		}

	}

?>