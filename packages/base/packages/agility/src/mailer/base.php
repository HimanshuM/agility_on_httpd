<?php

namespace Agility\Mailer;

use Agility\Configuration AS Config;
use Agility\Server\AbstractController;
use Agility\Templating\Render;
use ArrayUtils\Arrays;
use PHPMailer\PHPMailer\PHPMailer;

	class Base extends AbstractController {

		use Render;

		protected $defaults;
		protected $options;

		function __construct() {

			parent::__construct();

			$this->defaults = new Arrays;
			$this->options = new Arrays;

			$this->initializeTemplating();

		}

		protected function conclude($response) {

			if (!$this->_responded) {

				if (empty($this->_content)) {

					$content = $this->mail($response);
					$this->deliver($content);

				}
				else {
					$this->deliver($this->_content);
				}

			}

		}

		protected function defaults($def) {

			if (!is_a($def, Arrays::class)) {
				$def = new Arrays($def);
			}

			$this->defaults = $def;

		}

		protected function deliver($content) {

			$this->_responded = true;
			return new Delivery($content, $this->options);

		}

		static function initialize() {
			Config::mailer(new Configuration);
		}

		protected function mail() {

			$phpMailerObj = new PhpMailer(true);

			$template = false;
			$data = [];

			$args = func_get_args();
			foreach ($args as $arg) {

				if (is_string($arg) && empty($template)) {
					$template = $arg;
				}
				else if (is_array($arg) || is_a($arg, Arrays::class)) {
					$data = $arg;
				}

			}

			if (empty($template)) {
				$template = $this->methodInvoked;
			}

			$this->prepareOptions($data);

			$content = $this->renderEmail($template, $data);
			// $this->conclude($response);
			$this->_content = $phpMailerObj;

		}

		private function prepareOptions($data) {

			$invalid = [];

			$this->options["to"] = $data["to"] ?? false;
			if (emty($this->options["to"])) {
				$invalid[] = "to";
			}

			$this->options["from"] = $data["from"] ?? $this->defaults["from"] ?? false;
			if (empty($this->options["from"])) {
				$invalid[] = "from";
			}

			$this->options["subject"] = $data["subject"] ?? $this->defaults["subject"] ?? false;
			if (empty($this->options["subject"])) {
				$invalid[] = "subject";
			}

			$this->options["cc"] = $data["cc"] ?? $this->defaults["cc"] ?? false;
			$this->options["bcc"] = $data["bcc"] ?? $this->defaults["bcc"] ?? false;

			$this->options["attachments"] = $data["attachments"] ?? $this->defaults ?? [];

			if (!empty($invalid)) {
				throw new Exceptions\InsufficientMailerDataException($invalid);
			}

		}

		private function renderEmail($phpMailerObj, $template, $data) {

			$html = $this->renderHtml($template, $data);
			if (!empty($html)) {
				// $phpMailerObj->;
			}
			$text = $this->renderText($template, $data);

		}

		private function renderHtml($template, $data) {
			return $this->render(["partial" => $template.".html", "no_error" => true, "local" => $data]);
		}

		private function renderText($template, $data) {
			return $this->render(["partial" => $template.".text", "no_error" => true, "local" => $data]);
		}

	}

?>