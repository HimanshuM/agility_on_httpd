<?php

namespace Agility\Console\Helpers;

	trait EchoHelper {

		protected function echo($str, $force = false) {

			$str = trim($str, "\n");
			if (!$this->quite || $force) {
				OutputHelper::echo($str."\n");
			}

		}

		protected function die($str, $formatted = false) {

			$this->quite = false;

			if (!$formatted) {
				$str = "#B##Red#".$str."#N#";
			}

			$this->echo($str);
			die;

		}

	}

?>