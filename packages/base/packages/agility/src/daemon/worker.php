<?php

namespace Agility\Daemon;

	final class Worker {

		protected $name;
		protected $pollTimeout = 5;

		function __construct($name, $pollTimeout = 5) {

			$this->name = $name;

			if (empty($pollTimeout) || !is_numeric($pollTimeout)) {
				$pollTimeout = 5;
			}
			$this->pollTimeout = $pollTimeout;

		}

		function start() {

			while (1) {

				$this->poll();
				sleep($this->pollTimeout);

			}

		}

		protected function poll() {

		}

	}

?>