<?php

namespace Agility\Console\Helpers;

	class OutputHelper {

		/*const TerminalProps = [
			"#B#" => "\033[1m",
			"#W#" => "\033[37m",
			"#Bl#" => "\033[m",
			"#N#" => "\033(B\033[m",
		];*/

		const TerminalProps = [
			"#N#" 			=> "\e[0m",
			"#B#"			=> "\e[1m",
			"#U#" 			=> "\e[4m",
			"#Bl#"			=> "\e[5m",
			"#Black#"		=> "\e[30m",
			"#Red#"			=> "\e[31m",
			"#Green#"		=> "\e[32m",
			"#Yellow#"		=> "\e[33m",
			"#Blue#"		=> "\e[34m",
			"#Magenta#"		=> "\e[35m",
			"#Cyan#"		=> "\e[36m",
			"#Gray#"		=> "\e[37m",
			"#DGray#"		=> "\e[90m",
			"#LRed#"		=> "\e[91m",
			"#LGreen#"		=> "\e[92m",
			"#LYellow#"		=> "\e[93m",
			"#LBlue#"		=> "\e[94m",
			"#LMagenta#"	=> "\e[95m",
			"#LCyan#"		=> "\e[96m",
			"#White#"		=> "\e[97m",
		];

		static function echo($str) {

			foreach (OutputHelper::TerminalProps as $key => $value) {
				$str = str_replace($key, $value, $str);
			}

			echo $str;

		}

	}

?>