<?php

namespace Agility;

use ArrayUtils\Arrays;
use FileSystem\FileSystem;
use ReflectionClass;
use StringHelpers\Str;
use Swoole;

	class AppLoader {

		static function executeApp($argv) {

			$root = FileSystem::path("/");

			$cwd = FileSystem::cwd();
			while ($cwd != $root || $cwd->has("bin/agility") !== false) {

				if ($cwd->has("bin/agility")) {

					$appPath = $cwd->cwd."/bin/agility";
					// Swoole\Event::defer(function() use ($appPath, $argv) {
					passthru("exec $appPath ".$argv->implode(" "));
					// });
					die;

				}

				$cwd->chdir("..");

			}

			return false;

		}

		static function listApplicationModels() {
			return AppLoader::loadModels(true);
		}

		protected static function iterateThroughModels($children, $return = false) {

			$models = [];
			foreach ($children as $model) {

				if ($model->isFile) {

					if (($model = AppLoader::tryLoadingModel($model, $return)) != false) {
						$models[] = $model;
					}

				}
				else {
					array_merge($models, AppLoader::iterateThroughModels($model->children, $return));
				}

			}

			return $models;

		}

		static function loadModels($return = false) {

			$models = Configuration::documentRoot()->children("app/models");
			return AppLoader::iterateThroughModels($models, $return);

		}

		static function setupApplicationAutoloader($cwd) {

			spl_autoload_register(function($class) use ($cwd) {

				$components = new Arrays(explode("\\", $class));
				$class = $components->map(function($each) {
					return Str::snakeCase(lcfirst($each));
				})->implode("/");
				if (file_exists($cwd."/".$class.".php")) {
					require_once($cwd."/".$class.".php");
				}

			});

		}

		protected static function tryLoadingModel($modelFile, $return = false) {

			$modelClass = substr($modelFile, strpos($modelFile, "app/models/") + strlen("app/models/"), -4);
			$modelClass = "App\\Models\\".Str::normalize($modelClass);
			if (class_exists($modelClass)) {

				$classInfo = new ReflectionClass($modelClass);
				if (!$classInfo->isAbstract() && method_exists($modelClass, "staticInitialize") && is_subclass_of($modelClass, Data\Model::class)) {

					if ($return) {
						return $modelClass;
					}

					$modelClass::staticInitialize();

				}

			}

			return false;

		}

	}

?>