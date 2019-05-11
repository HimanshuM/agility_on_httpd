<?php

namespace Phpm\UnitTesting;

use FileSystem\Dir;
use FileSystem\File;
use StringHelpers\Str;

	class Tester {

		private $_tests;

		function __construct($tests) {

			$this->_tests = $tests;
			// spl_autoload_register("register_testsuites_autoload");

		}

		private function _invokeTest($class, $method) {

			if (!class_exists($class)) {

				echo "Could not find test suit '$class'. Skipping...\n\n";
				return null;

			}

			$outcome;
			$obj = new $class;

			$method = "perform".$method;
			$outcome = $obj->$method();

			return $outcome;

		}

		function perform() {

			$outcomes = [];
			$dir = Dir::cwd();

			foreach ($this->_tests as $key => $test) {

				$test = explode(":", $test);
				$method = "All";

				if (!empty($test[1])) {
					$method = $test[1];
				}
				$test = $test[0];

				if (strpos($test, ".php") === false) {
					$testFile = $test.".php";
				}
				if (($testFile = $dir->has($testFile)) === false) {

					echo "Could not find test suite '".Str::camelCase($test)."' in the current working directory.";
					return;

				}

				$test = File::open($testFile);
				if ($test->extension != "php") {
					continue;
				}

				$testName = substr($test->basename, 0, -4);

				echo ($key + 1).". Performing test: '$testName'...\n\n";
				require_once $test->path;

				$return = $this->_invokeTest(Str::camelCase($testName), Str::camelCase($method));
				if ($return == null) {
					continue;
				}

				$outcomes[] = $return;

				if ($key < count($this->_tests) - 1) {
					echo "============================================\n\n";
				}

			}

			$this->_printOutcome($outcomes);

		}

		private function _printOutcome($outcomes) {

			if (empty($outcomes)) {
				return;
			}

			$fail = 0;
			$success = 0;
			$assertions = 0;
			foreach ($outcomes as $test) {

				$fail += $test[0];
				$success += $test[1];
				$assertions += $test[2];

			}

			echo ($fail > 0 ? ("FAILURE".($fail > 1 ? "S" : "")."!") : "SUCCESS!")."\n";
			echo ("Test".(count($outcomes) > 1 ? "s" : "").": ".count($outcomes)).", Assertion".($assertions > 1 ? "s" : "").": ".$assertions.", Success".($success > 1 ? "es" : "").": ".$success.", Failure".($fail != 1 ? "s" : "").": ".$fail."\n";

		}

	}

?>