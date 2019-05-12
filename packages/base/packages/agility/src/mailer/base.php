<?php

namespace Agility\Mailer;

use Agility\Configuration AS Config;
use Agility\Server\AbstractController;
use Agility\Templating\Render;
use ArrayUtils\Arrays;
use PHPMailer\PHPMailer\PHPMailer;

	class Base extends AbstractController {

		use Helper;
		use Render;
		use EmailTags;

		protected $defaults;
		protected $options;
		protected $assetHost;
		protected $urlHost;

		protected static $_interceptors = [];

		function __construct() {

			parent::__construct();

			$this->assetHost = Config::mailer()->assetHost;
			$this->urlHost = Config::mailer()->urlHost;

			$this->defaults = new Arrays;
			$this->options = new Email;

			$this->initializeTemplating();

		}

		protected function conclude($response) {

			if (!$this->_responded) {

				if (empty($this->_content)) {

					$content = $this->mail($response);
					return $this->deliver($content);

				}
				else {
					return $this->deliver($this->_content);
				}

			}

		}

		protected function defaults($def) {

			if (!is_a($def, Arrays::class)) {
				$def = new Arrays($def);
			}

			$this->defaults = $def;

		}

		protected function deliver() {

			$this->_responded = true;
			Base::invokeInterceptors($this);

			return new Delivery($this->options);

		}

		static function initialize() {
			Config::mailer(new Configuration);
		}

		static function invokeInterceptors($mailer) {

			foreach (Base::$_interceptors as $interceptor) {
				call_user_func_array($interceptor, [$mailer]);
			}

		}

		protected function mail() {

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

			if (!empty($data["body"])) {

				if (is_string($data["body"])) {
					$this->options->setText($data["body"]);
				}
				else {

					if (!empty($data["body"]["html"])) {
						$this->options->setHtml($data["body"]);
					}
					if (!empty($data["body"]["text"])) {
						$this->options->setText($data["text"]);
					}

				}

			}
			else {
				$this->renderEmail($template, $data);
			}

			return $this->_content = $this->options;

		}

		private function prepareOptions($data) {

			$invalid = [];

			if (!$this->setFrom($data)) {
				$invalid[] = "from";
			}
			$this->setReplyTo($data);

			$this->options->setSubject($data["subject"] ?? $this->defaults["subject"] ?? false);
			if (empty($this->options->subject)) {
				$invalid[] = "subject";
			}

			if (!$this->addTo($data)) {
				$invalid[] = "to";
			}

			$this->addCc($data);
			$this->addBcc($data);

			if (!empty($invalid)) {
				throw new Exceptions\InsufficientMailerDataException($invalid);
			}

		}

		static function registerInterceptor($callback) {
			Base::$_interceptors[] = $callback;
		}

		private function renderEmail($template, $data) {

			$this->options->setHtml($this->renderHtml($template, $data));
			$this->options->setText($this->renderText($template, $data));

		}

		private function renderHtml($template, $data) {
			return $this->render(["partial" => $template.".html", "no_error" => true, "local" => $data]);
		}

		private function renderText($template, $data) {
			return $this->render(["partial" => $template.".text", "no_error" => true, "local" => $data]);
		}

	}

?>