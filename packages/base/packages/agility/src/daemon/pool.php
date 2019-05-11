<?php

namespace Agility\Daemon;

	final class Pool {

		protected $workerQueue = [];
		protected $maxWorkers = 4;
		protected $pollTimeout = 5;

		private static $_instance = null;

		private function __construct($maxWorkers, $pollTimeout) {

			$this->maxWorkers = $maxWorkers;
			$this->pollTimeout = $pollTimeout;

		}

		static function initialize($maxWorkers = 4, $pollTimeout = 5) {

			if (!is_null(Pool::$_instance)) {
				return Pool::$_instance;
			}

			if (!is_numeric($maxWorkers) || empty($maxWorkers)) {
				$maxWorkers = 4;
			}
			if (!is_numeric($pollTimeout) || empty($pollTimeout)) {
				$pollTimeout = 5;
			}

			Pool::$_instance = new Pool($maxWorkers, $pollTimeout);
			Pool::$_instance->launch();

			return Pool::$_instance;

		}

		protected function launch() {

		}

		function maxWorkers() {
			return $this->maxWorkers;
		}

		function pollTimeout() {
			return $this->pollTimeout;
		}

	}

?>