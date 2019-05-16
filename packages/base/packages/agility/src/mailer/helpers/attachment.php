<?php

namespace Agility\Mailer\Helpers;

	class Attachment {

		public $source;
		public $name;
		public $isFile = true;
		public $contentId = false;
		public $encoding = "base64";
		public $mime = "";

		function fill($phpMailer) {

			if (!empty($this->contentId)) {
				$phpMailer->addEmbeddedImage($this->source, $this->contentId, $this->name, $this->encoding, $this->mime);
			}
			elseif (!$this->isFile) {
				$phpMailer->addStringAttachment($this->source, $this->name, $this->encoding, $this->mime);
			}
			else {
				$phpMailer->addAttachment($this->source, $this->name, $this->encoding, $this->mime);
			}

		}

		function url() {
			return "cid:".$this->contentId;
		}

	}

?>