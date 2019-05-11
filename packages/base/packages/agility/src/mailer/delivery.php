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

		function __construct($content, $options) {

			$this->content = $content;

			if (!is_a($options, Arrays::class)) {
				$options = new Arrays($options);
			}
			$this->options = $options;

		}

		private function applicationInstance() {
			$this->applicationInstance = Application::instance();
		}

		function sendMail() {

		}

		function sendAt($when = "now") {

			if ($when == "now") {
				$this->sendNow();
			}

		}

		function sendLater($after = 0) {
			Swoole\Timer::after($after, [$this, "sendMail"]);
		}

		function sendNow() {
			Swoole\Event::defer([$this, "sendMail"]);
		}

	}

?>