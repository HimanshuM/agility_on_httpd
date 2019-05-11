<?php

namespace Agility\Data\Helpers;

use ArrayUtils\Arrays;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class NameHelper {

		static function getNamespace($className) {

			$className = new Arrays(explode("\\", $className));
			return $className->firstFew(-1)->implode("\\");

		}

		static function getStorableName($name) {
			return Str::snakeCase($name);
		}

		static function classify($tableName, $namespaceFrom = false, $singularize = true) {

			$tableName = $singularize ? Inflect::singularize($tableName) : $tableName;
			$namespace = NameHelper::getNamespace(empty($namespaceFrom) ? $tableName : $namespaceFrom);

			return (!empty($namespace) ? $namespace."\\" : "").Str::camelCase($tableName);

		}

		static function tablize($model) {

			$model = str_replace("App\\Models\\", "", $model);
			$model = new Arrays(explode("\\", $model));

			$model->last = Inflect::pluralize($model->last);
			return Str::snakeCase($model->implode(""));

		}

	}

?>