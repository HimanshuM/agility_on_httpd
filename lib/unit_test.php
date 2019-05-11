<?php

namespace Phpm;

use Exception;
use StringHelpers\Str;

	class UnitTest {

		private $result = [];

		private $methodReflections = [];

		function __construct() {

		}

		function __call($method, $args = []) {

			if (strpos($method, "perform") === 0) {

				$method = "test".Str::camelCase(substr($method, 7));
				return $this->performTests([$method]);

			}
			else {
				throw new Exception("Method '$method' does not exist in class Phpm\\UnitTest", 1);
			}

		}

		function assertArray($expected, $actual = null) {

			$result = false;
			if ($actual === null) {
				$result = is_array($expected);
			}
			else {
				$result = $expected == $actual;
			}
			$msg = $result ? "" : "Failed to assert that ".($actual === null ? "array is received" : "two arrays are equal");
			$this->result[] = [$result, $actual === null ? "Array" : $this->getActual($expected), $actual === null ? "Array" : $this->getActual($actual), $this->getCaller(), $msg];

		}

		function assertEquals($expected, $actual) {

			$result = $expected === $actual;
			$msg = $result ? "" : "Failed to assert that '$actual' equals '$expected'.";
			$this->result[] = [$result, $expected, $this->getActual($actual), $this->getCaller(), $msg];

		}

		function assertNull($actual) {

			$result = $actual === null;
			$msg = $result ? "" : "Failed to assert that null is received.";
			$this->result[] = [$result, "NULL", $actual, $this->getCaller(), $msg];

		}

		function expectExceptionMessage($excerpt, $message) {

			$result = strpos(strtolower($message), strtolower($excerpt)) !== false;
			$msg = $result ? "" : "Failed to assert that an exception with message containing '$excerpt' was thrown.";
			$this->result[] = [$result, "An exception with message containing '$excerpt'", $message, $this->getCaller(), $msg];

		}

		function getActual($actual) {

			if (is_null($actual)) {
				return "NULL";
			}
			else if (is_bool($actual)) {
				return $actual ? "TRUE" : "FALSE";
			}
			else if (is_scalar($actual)) {
				return $actual;
			}
			else /*if (is_array($actual))*/ {
				return print_r($actual, true);
			}

		}

		function getCaller() {

			$trace = debug_backtrace();
			$caller = $trace[2];

			return $caller["class"]."::".$caller["function"];

		}

		function getDocComment($method) {

			if (isset($this->methodReflection[$method])) {
				return $this->methodReflection[$method];
			}

			$this->methodReflection[$method] = new \ReflectionMethod($this, $method);
			return $this->methodReflection[$method]->getDocComment();

		}

		function getDataProvider($method) {

			$docComment = $this->getDocComment($method);
			$matches = [];
			if (!preg_match("/\@dataProvider\s(\w)+/", $docComment, $matches)) {
				return false;
			}

			return trim(substr($matches[0], strlen("@dataProvider")));

		}

		function invokeDataProvider($method) {

			if (($dataProvider = $this->getDataProvider($method)) !== false) {
				return $this->$dataProvider();
			}

			return false;

		}

		function invokeTest($method) {

			$data = $this->invokeDataProvider($method);

			$this->setUp();

			if (!empty($data)) {

				foreach ($data as $datum) {
					call_user_func_array([$this, $method], $datum);
				}

			}
			else {
				$this->$method();
			}

			$this->tearDown();

		}

		function performAll() {

			$methodsToInvoke = [];

			$methods = get_class_methods($this);
			foreach ($methods as $method) {

				if (strpos($method, "test") === 0) {
					$methodsToInvoke[] = $method;
				}

			}

			return $this->performTests($methodsToInvoke);

		}

		function performTests($methodsToInvoke) {

			$this->reset();

			$start = microtime(true);

			foreach ($methodsToInvoke as $method) {
				$this->invokeTest($method);
			}

			$end = microtime(true);

			echo "\nTime: ".ceil(($end - $start) / 1000)." ms. Memory: ".(ceil(memory_get_usage()/1024))." kB.\n\n";

			$fail = 0;
			$success = 0;

			foreach ($this->result as $result) {

				echo "Test: ".$result[3]."\n\n";
				if (!$result[0]) {

					echo "Status: Failure!\n";
					if (!empty($result[4])) {
						echo $result[4]."\n";
					}
					echo "Expected: ".$result[1].",\n";
					echo "Found: ".$result[2].".\n";

					$fail++;

				}
				else {

					echo "Status: Success\n";
					$success++;

				}

				echo "============================\n\n";

			}

			return [$fail, $success, count($this->result)];

		}

		private function reset() {
			$this->result = [];
		}

		protected function setUp() {

		}

		protected function tearDown() {

		}

	}

?>