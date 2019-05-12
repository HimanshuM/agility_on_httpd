<?php

namespace Agility\Mailer\Helpers;

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

			if (is_array($receipients) && isset($receipients[0])) {

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

		function addInlineAttachment($name, $source, $options = []) {

			$options["name"] = $name;
			$attachment = $this->createAttachment($source, $options);

			$lastContentId = 999;
			$lastInlineAttachment = $this->inlineAttachments->last;
			if (!empty($lastInlineAttachment)) {
				$lastContentId = substr($lastInlineAttachment->contentId, 3);
			}

			$attachment->contentId = "CID".$lastContentId + 1;

			$this->inlineAttachments[$attachment->name] = $attachment;

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

		function fill($phpMailer) {

			$this->from->fill($phpMailer, "from");

			if (!empty($this->replyTo)) {
				$this->replyTo->fill($phpMailer, "replyTo");
			}

			foreach ($this->to as $to) {
				$to->fill($phpMailer, "to");
			}

			foreach ($this->cc as $cc) {
				$cc->fill($phpMailer, "cc");
			}

			foreach ($this->bcc as $bcc) {
				$bcc->fill($phpMailer, "bcc");
			}

			$phpMailer->Subject = $this->subject;
			$this->fillAttachments($phpMailer);

			$text = "Body";
			if (!empty($this->html)) {

				$phpMailer->Body = $this->html;
				$phpMailer->isHTML(true);
				$text = "AltBody";

			}
			if (!empty($this->text)) {
				$phpMailer->$text = $this->text;
			}

		}

		protected function fillAttachments($phpMailer) {

			foreach ($this->attachments as $attachment) {
				$attachment->fill($phpMailer);
			}

			foreach ($this->inlineAttachments as $attachment) {
				$attachment->fill($phpMailer);
			}

		}

		function getAttachmentbyName($name) {

			if (!empty($this->inlineAttachments[$name])) {
				return $this->inlineAttachments[$name];
			}

			return false;

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

		function setFrom($from) {

			if (!empty($from)) {
				$this->from = $this->parse($from);
			}

			return $this;

		}

		function setHtml($html) {
			$this->html = $html;
		}

		function setReplyTo($replyTo) {

			if (!empty($replyTo)) {
				$this->replyTo = $this->parse($replyTo);
			}

			return $this;

		}

		function setSubject($subject) {
			$this->subject = $subject;
		}

		function setText($text) {
			$this->text = $text;
		}

	}

?>