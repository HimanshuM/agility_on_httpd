<?php

namespace Agility\Console;

use Agility\AppLoader;
use ArrayUtils\Arrays;
use Error;
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

			$namespaces = static::configureNamespaces();

			if (($result = self::lookup($command, $namespaces)) === false) {

				echo "Command '$command' not found.\n";
				return;

			}

			static::invokeCommand($result);

			echo "\n";

		}

		static function configureNamespaces() {

			$namespaces = ["Agility\\Console\\Commands" => FileSystem::path(__DIR__."/commands")];

			if (defined("APP_PATH")) {

				$appPath = FileSystem::path(APP_PATH);
				$appPath = $appPath->cwd->chdir("..");

				AppLoader::setupApplicationAutoloader($appPath);

				$namespaces["Lib\\Tasks"] = [FileSystem::path($appPath->has("lib/tasks"))];

			}

			return $namespaces;

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

			foreach ($namespaces as $namespace => $path) {

				$classPath = $class;
				if ($namespace == "Agility\\Console\\Commands") {
					$classPath .= "_command";
				}

				$tryClass = Str::camelCase($classPath);

				if ($path->has($classPath.".php") && method_exists($namespace."\\".$tryClass, $method)) {
					return [$namespace."\\".$tryClass, $method];
				}

			}

			return false;

		}

		protected static function invokeCommand($arg) {

			list($class, $method) = $arg;

			// A class present inside Commands or lib/tasks directory can register itself as hidden from it's constructor by invoking Agility\Command::hidden("<namespace\class_name>"");
			if (self::$_hiddenCommands->has($class)) {
				echo "'$command' does not exist. Please type 'agility help' to see the list of available commands.";
			}
			else {

				try {

					$object = new $class;
					$object->$method($args);

				}
				catch (Exception $e) {

					echo $e->getMessage()."\n";
					echo $e->getTraceAsString();

				}
				catch (Error $e) {

					echo $e->getMessage()."\n";
					echo $e->getTraceAsString();

				}

			}

		}

	}

?>