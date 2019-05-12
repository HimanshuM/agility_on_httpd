<?php

namespace Agility\Mailer;

use Agility\Application;
use Agility\Chrono\Chronometer;
use ArrayUtils\Arrays;
use Swoole;

	class Delivery {

		protected $content;
		protected $options;
		protected $applicationInstance;

		function __construct($options) {
			$this->options = $options;
		}

		private function applicationInstance() {
			$this->applicationInstance = Application::instance();
		}

		function sendMail() {

			$phpMailerObj = new PhpMailer(true);
			$this->options->fill($phpMailerObj);

			if (Config::mailer()->deliveryMethod == "sendmail") {
				$phpMailerObj->isSendmail();
			}
			elseif (Config::mailer()->deliveryMethod != "mail") {
				$phpMailerObj->isSMTP();
			}

		}

		function sendAt($when = "now") {

			if ($when == "now") {
				$this->sendNow();
			}

		}

		function sendAsync() {

		}

		function sendNow() {
			$this->sendMail();
		}

	}

?>