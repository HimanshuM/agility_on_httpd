<?php

namespace Agility\Mailer;

use AttributeHelper\Accessor;
use ArrayUtils\Arrays;

	class EmailOptions {

		use Accessor;

		protected $from;
		protected $to = [];
		protected $cc = [];
		protected $bcc = [];
		protected $replyTo;
		protected $subject = "";
		protected $html = "";
		protected $text = "";
		protected $attachments;
		protected $inlineAttachments;

		function __construct() {

			$this->attachments = new Arrays;
			$this->inlineAttachments = new Arrays;

			$this->readonly("from", "fromName", "to", "cc", "bcc", "replyTo", "subject", "html", "text", "attachments", "inlineAttachments");

		}

		protected function add($attr, $receipients) {

			if (isset($receipients[0])) {

				foreach ($receipients as $receipient) {
					$this->$attr[] = $this->parse($receipint);
				}

			}
			else {
				$this->$attr[] = $this->parse($receipients);
			}

			return $this;

		}

		function addAttachment($source, $options = []) {
			$this->attachments[] = $this->createAttachment($source, $options);
		}

		function addBcc($bcc) {
			return $this->add("bcc", $bcc);
		}

		function addCc($cc) {
			return $this->add("cc", $cc);
		}

		function addInlineAttachment($source, $options = []) {

			$attachment = $this->createAttachment($source, $options);

			$lastContentId = 999;
			$lastInlineAttachment = $this->inlineAttachments->last;
			if (!empty($lastInlineAttachment)) {
				$lastContentId = substr($lastInlineAttachment->contentId, 3);
			}

			$attachment->contentId = "CID".$lastContentId + 1;

			$this->inlineAttachments[] = $attachment;

			return $attachment;

		}

		function addTo($to) {
			return $this->add("to", $to);
		}

		private function createAttachment($source, $options) {

			$attachment = new Attachment;

			$attachment->source = $source;
			$attachment->isFile = !empty($options["raw"]);

			if (!empty($options["name"])) {
				$attachment->name = $options["name"];
			}
			if (!empty($options["encoding"])) {
				$attachment->encoding = $options["encoding"];
			}
			if (!empty($options["mime"])) {
				$attachment->mime = $options["mime"];
			}

			return $attachment;

		}

		private function parse($info) {

			$email = "";
			$name = "";
			if (is_array($info)) {

				$email = $info["email"] ?? $info[0] ?? false;
				$name = $info["name"] ?? $info[1] ?? false;

			}
			else {
				$email = $info;
			}

			return new Email($email, $name);

		}

		function setFrom($from, $default) {

			if (!empty($from)) {
				$this->from = $this->parse($from);
			}
			elseif (!empty($default)) {
				$this->from = $this->parse($default);
			}

			return $this;

		}

		function setReplyTo($replyTo, $default) {

			if (!empty($replyTo)) {
				$this->replyTo = $this->parse($replyTo);
			}
			elseif (!empty($default)) {
				$this->replyTo = $this->parse($default);
			}

			return $this;

		}

		function setSubject($subject) {
			$this->subject = $subject;
		}

	}

?>