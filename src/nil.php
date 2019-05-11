<?php

namespace Phpm;

	class Nil {

		private static $_instance = null;
		private $_nil = null;

		private function __construct() {

		}

		static function nil() {

			if (empty(Nil::$_instance)) {
				Nil::$_instance = new Nil;
			}

			return Nil::$_instance;

		}

		function __toString() {
			return "NULL";
		}

	}

	if (!defined("nil")) {
		define ("nil", Nil::nil());
	}

?>