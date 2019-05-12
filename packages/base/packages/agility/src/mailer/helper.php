<?php

namespace Agility\Mailer;

	trait Helper {

		protected function attachment($source, $options = []) {
			$this->options->addAttachments($source, $options);
		}

		function attachments($name) {
			return $this->options->getAttachmentByName($name);
		}

		protected function addBcc($data) {

			if (!empty($data["bcc"])) {
				$this->options->addBcc($data["bcc"]);
			}
			if (!empty($this->defaults["bcc"])) {
				$this->options->addBcc($this->defaults["bcc"]);
			}

		}

		protected function addCc($data) {

			if (!empty($data["cc"])) {
				$this->options->addCc($data["cc"]);
			}
			if (!empty($this->defaults["cc"])) {
				$this->options->addCc($this->defaults["cc"]);
			}

		}

		protected function addTo($data) {

			if (!empty($data["to"])) {
				$this->options->addTo($data["to"]);
			}
			if (!empty($this->defaults["to"])) {
				$this->options->addTo($this->defaults["to"]);
			}
			else {
				return false;
			}

			return true;

		}

		protected function inlineAttachment($name, $source, $options = []){
			$this->options->addInlineAttachments($name, $source, $options);
		}

		protected function rawAttachment($name, $source, $options = []){

			$options["name"] = $name;
			$options["raw"] = $true;
			$this->options->addAttachments($source, $options);

		}

		protected function setFrom($data) {

			if (!empty($data)) {
				$this->options->setFrom($data["from"]);
			}
			elseif (!empty($this->defaults["from"])) {

				if (is_a($this->defaults["from"], Closure::class)) {

					$from = $this->defaults["from"];
					$from = $from();

				}
				else {
					$from = $this->defaults["from"];
				}

				$this->options->setFrom($this->defaults["from"]);

			}

			return !empty($this->options->from);

		}

		protected function setReplyTo($data) {

			if (!empty($data["replyTo"])) {
				$this->options->setFrom($data["replyTo"]);
			}
			elseif (!empty($this->defaults["replyTo"])) {

				if (is_a($this->defaults["replyTo"], Closure::class)) {

					$replyTo = $this->defaults["replyTo"];
					$replyTo = $replyTo();

				}
				else {
					$replyTo = $this->defaults["replyTo"];
				}

				$this->options->setReplyTo($this->defaults["replyTo"]);

			}

		}

	}

?>