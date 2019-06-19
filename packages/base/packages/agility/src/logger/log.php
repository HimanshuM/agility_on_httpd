<?php

namespace Agility\Logger;

use Agility\Configuration;
use FileSystem\File;
use Phpm\Exceptions\ClassExceptions\ClassNotFoundException;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	class Log {

		const LogLevels = [
			"emergency" => 0,
			"alert" => 1,
			"critical" => 2,
			"error" => 3,
			"warning" => 4,
			"notice" => 5,
			"info" => 6,
			"debug" => 7,
			"db" => 8,
		];

		private static $loggerName;
		private static $instance;

		protected function __construct() {
			$this->logDir = Configuration::logPath();
		}

		static function alert($message, $context = []) {
			Log::$instance->alert($message, $context);
		}

		static function critical($message, $context = []) {
			Log::$instance->critical($message, $context);
		}

		static function debug($message, $context = []) {
			Log::$instance->debug($message, $context);
		}

		static function emergency($message, $context = []) {
			Log::$instance->emergency($message, $context);
		}

		static function error($message, $context = []) {
			Log::$instance->error($message, $context);
		}

		static function initialize() {

			if (is_null(Configuration::logLevel())) {
				Configuration::logLevel(6);
			}

			Log::initializeLogPaths();
			Log::initializeAppropriateLogger();

		}

		static function initializeAppropriateLogger() {

			if (empty(Log::$instance)) {

				if (interface_exists("Psr\\Log\\LoggerInterface") && class_exists("Psr\\Log\\LogLevel")) {
					Log::$loggerName = "Agility\\Logger\\Psr\\Log";
				}
				else {
					Log::$loggerName = "Agility\\Logger\\FallbackLogger";
				}

				$loggerName = Log::$loggerName;
				Log::$instance = new $loggerName;

			}

		}

		static function initializeLogPaths() {

			if (!Configuration::documentRoot()->has("log") && !Configuration::documentRoot()->mkdir("log")) {
				$this->die("Failed to create log directory. Make sure the document root is writable.");
			}

			$logPath = Configuration::documentRoot()->chdir("log");
			Configuration::logPath($logPath);

			Log::setupLogFiles($logPath);

		}

		static function info($message, $context = []) {
			Log::$instance->info($message, $context);
		}

		static function log($level, $message, $context = []) {
			Log::$instance->log($level, $message, $context);
		}

		static function notice($message, $context = []) {
			Log::$instance->notice($message, $context);
		}

		static function prepareLogFile($setting, $value) {

			if (!is_a($value, File::class)) {
				$value = File::open($value);
			}

			return $value;

		}

		static function register($className) {

			if (!class_exists($className)) {
				throw new ClassNotFoundException("Agility\\Logger\\Log::register()", $className);
			}

			$instance = new $className;
			if (!is_a(Log::$instance, "Psr\\Log\\LoggerInterface") && !is_a(Log::$instance, "Agility\\Logger\\Psr\\LoggerInterface")) {
				throw new InvalidArgumentTypeException("Agility\\Logger\\Log::register()", 1, ["Psr\\Log\\LoggerInterface", "Agility\\Logger\\Psr\\LoggerInterface"], get_class($instance));
			}

			Log::$loggerName = $className;
			Log::$instance = $instance;

		}

		static function setupLogFiles($logPath) {

			if (($dbLog = $logPath->touch("db.log")) === false) {
				die("Db log file could not be created. Please make sure the log directory is writable.");
			}
			if (($errorLog = $logPath->touch("error.log")) === false) {
				die("Error log file could not be created. Please make sure the log directory is writable.");
			}
			if (($infoLog = $logPath->touch("info.log")) === false) {
				die("Info log file could not be created. Please make sure the log directory is writable.");
			}

			Configuration::dbLog($dbLog, [Log::class, "prepareLogFile"]);
			Configuration::errorLog($errorLog, [Log::class, "prepareLogFile"]);
			Configuration::infoLog($infoLog, [Log::class, "prepareLogFile"]);

		}

		static function warning($message, $context = []) {
			Log::$instance->warning($message, $context);
		}

	}

?>