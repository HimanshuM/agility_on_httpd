<?php

namespace Agility\Console;

use Agility\AppLoader;
use ArrayUtils\Arrays;
use Exception;
use FileSystem\FileSystem;
use StringHelpers\Str;

	class Command {

		static private $_hiddenCommands = null;

		static function hidden($className) {

			if (is_null(self::$_hiddenCommands)) {
				self::$_hiddenCommands = new Arrays;
			}

			self::$_hiddenCommands[] = $className;

		}

		static function invoke($command, $args) {

			static::hidden("Agility\\Console\\Commands\\Base");

			$namespaces = [[FileSystem::path(__DIR__."/commands"), "Agility\\Console\\Commands"]];
			if (defined("APP_PATH")) {

				$appPath = FileSystem::path(APP_PATH);
				$appPath = $appPath->cwd->chdir("..");

				AppLoader::setupApplicationAutoloader($appPath);

				$namespaces[] = [FileSystem::path($appPath->has("lib/tasks")), "Lib\\Tasks"];

			}

			if (($result = self::lookup($command, $namespaces)) === false) {

				echo "Command '$command' not found.\n";
				return;

			}

			list($class, $method) = $result;

			// A class present inside Commands or lib/tasks directory can register itself as hidden from it's constructor by invoking Agility\Command::hidden("<namespace\class_name>"");
			if (self::$_hiddenCommands->has($class)) {
				echo "'$command' does not exist. Please type 'agility help' to see the list of available commands.";
			}
			else {

				$object = new $class;

				try {
					$object->$method($args);
				}
				catch (Exception $e) {

					echo $e->getMessage()."\n";
					echo $e->getTraceAsString();

				}

			}

			echo "\n";

		}

		static function lookup($command, $namespaces) {

			if (strpos($command, ":") !== false) {
				list($class, $method) = explode(":", $command);
			}
			else {

				$class = $command;
				$method = "perform";

			}

			$method = Str::pascalCase($method);

			foreach ($namespaces as $namespace) {

				$classPath = $class;
				if ($namespace[1] == "Agility\\Console\\Commands") {
					$classPath .= "_command";
				}

				$tryClass = Str::camelCase($classPath);

				if ($namespace[0]->has($classPath.".php") && method_exists($namespace[1]."\\".$tryClass, $method)) {
					return [$namespace[1]."\\".$tryClass, $method];
				}

			}

			return false;

		}

	}

?>