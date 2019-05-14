<?php

namespace Agility\Mailer;

use Closure;

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

				$bcc = $this->defaults["bcc"];
				if (is_a($bcc, Closure::class)) {
					$bcc = $bcc();
				}

				$this->options->addBcc($bcc);

			}

		}

		protected function addCc($data) {

			if (!empty($data["cc"])) {
				$this->options->addCc($data["cc"]);
			}
			if (!empty($this->defaults["cc"])) {

				$cc = $this->defaults["cc"];
				if (is_a($cc, Closure::class)) {
					$cc = $cc();
				}

				$this->options->addCc($cc);

			}

		}

		protected function addTo($data) {

			if (!empty($data["to"])) {
				$this->options->addTo($data["to"]);
			}
			if (!empty($this->defaults["to"])) {

				$to = $this->defaults["to"];
				if (is_a($to, Closure::class)) {
					$to = $to();
				}

				$this->options->addTo($to);

			}

			return !empty($this->options->to);

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