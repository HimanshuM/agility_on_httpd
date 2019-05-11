<?php

namespace Agility\Mailer;

	class Attachment {

		public $source;
		public $name;
		public $isFile = true;
		public $contentId = false;
		public $encoding = false;
		public $mime = false;

		function addToPhpMailer($phpMailer) {

		}

	}

?>