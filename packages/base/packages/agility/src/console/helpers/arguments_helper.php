<?php

namespace Agility\Console\Helpers;

use Exception;
use ArrayUtils\Arrays;

	class ArgumentsHelper {

		static function parseOptions($args, $strict = false) {

			if (is_a($args, "ArrayUtils\\Arrays")) {
				$args = $args->array;
			}
			else if (is_string($args)) {
				$args = explode(" ", $args);
			}

			if (!is_array($args)) {
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				throw new Exception("Agility\\Console\\Helpers\\AsrgumentsHelper::parseOptions() expects a string, an array or an object of class ArrayUtils\\Arrays", 1);
			}

			$options = new Arrays;

			$i = 0;
			while ($i < count($args)) {

				if ($args[$i][0] == "-") {

					$key = $args[$i];

					if ($args[$i][1] == "-") {

						if (strpos($args[$i], "=") !== false) {
							list($key, $value) = explode("=", $args[$i]);
						}
						else {
							$value = true;
						}

					}
					else {
						list($value, $i) = self::getValue($args, $i + 1);
					}

					$options[$key] = $value;

				}
				else if (!$strict) {
					$options[$args[$i]] = true;
				}

				$i++;

			}

			return $options;

		}

		private static function getValue($args, $index) {

			$value = true;
			if (empty($args[$index])) {
				return [$value, $index];
			}

			if ($args[$index][0] == "\"" || $args[$index][0] == "'") {

				$start = $args[$index][0];
				while ($args[$index][count($args[$index]) - 1] != $start) {

					$value[] = $args[$index];
					$index++;

				}

				$value = implode(" ", $value);

			}
			else if ($args[$index][0] == "-") {
				return [$value, $index - 1];
			}
			else {
				$value = $args[$index];
			}

			return [$value, $index];

		}

	}

?>