<?php

namespace Agility\Data\Associations;

use Agility\Data\Metadata\AssociationStore;
use Agility\Data\Helpers\NameHelper;
use ArrayUtils\Arrays;
use Exception;
use Phpm\Exceptions\ClassExceptions\ClassNotFoundException;
use StringHelpers\Inflect;
use StringHelpers\Str;

	trait Builder {

		protected static $_associationsCache;

		protected static function belongsTo($associationName, $options = []) {

			$polymorphic = $options["polymorphic"] ?? false;

			$className = $associationName;
			if ($polymorphic === false) {

				if (!empty($options["className"])) {
					$className = "App\\Models\\".$options["className"];
				}
				else {
					$className = NameHelper::classify($associationName, static::class, false);
				}

				if (!class_exists($className)) {
					throw new Exception("Could not find class '$className'.", 1);
				}

				// $foreignKey = $options["foreignKey"] ?? Inflect::singularize($className::tableName())."Id";
				$foreignKey = $options["foreignKey"] ?? Str::pascalCase(Inflect::singularize($className::tableName())."Id");
				$primaryKey = $options["primaryKey"] ?? $className::$primaryKey;

			}
			else {

				$foreignKey = $className."Id";
				$primaryKey = "id";

			}

			static::belongsToAssociations()[$associationName] = new ParentAssociation($foreignKey, $className, $primaryKey, $polymorphic);

		}

		protected static function belongsToAssociations() {
			return static::associationsCache()->belongsToAssociations;
		}

		static function associationsCache() {
			return static::initializeAssociations();
		}

		protected static function hasMany($associationName, $options = [], $callback = null) {

			$precedent = "className";
			// $subsequent = "source";
			$subsequent = "className";

			$through = $options["through"] ?? null;

			$as = false;
			$sourceType = false;
			if (!empty($options["as"])) {

				$as = $options["as"];
				$options["foreignKey"] = $options["foreignKey"] ?? $as."_id";

			}
			else if (!empty($through)) {

				if (!static::hasManyAssociations()->exists($through)) {
					throw new Exceptions\HasManyThroughNotFoundException($through, static::class);
				}

				$through = static::hasManyAssociations()[$through];

				// $precedent = "source";
				// $subsequent = "className";

			}

			$associatedClass = null;
			$associatedName = $associationName;
			if (!empty($options[$precedent])) {

				$associatedClass = NameHelper::classify(Str::camelCase($options[$precedent]), static::class, true);
				$associatedName = $options[$precedent];

			}
			else if (!empty($options[$subsequent])) {

				$associatedClass = NameHelper::classify(Str::camelCase($options[$subsequent]), static::class, true);
				$associatedName = $options[$subsequent];

			}
			else {
				$associatedClass = NameHelper::classify($associationName, static::class);
			}

			if (!class_exists($associatedClass)) {
				throw new ClassNotFoundException($associatedClass);
			}

			$primaryKey = $options["primaryKey"] ?? static::$primaryKey;
			$foreignKey = Str::snakeCase($options["foreignKey"] ?? Inflect::singularize(static::tableName())."_id");

			$source = $options["source"] ?? null;
			$sourceType = str_replace("App\\Models\\", "", $options["sourceType"] ?? $associatedClass);

			static::hasManyAssociations()[$associationName] = new DependentAssociation($associationName, static::class, $primaryKey, $associatedClass, $associatedName, $foreignKey, $through, $as, $source, $sourceType, $callback);

		}

		protected static function hasManyAssociations() {
			return static::associationsCache()->hasManyAssociations;
		}

		protected static function hasAndBelongsToMany() {

		}

		protected static function hasAndBelongsToManyAssociations() {
			return static::associationsCache()->hasAndBelongsToManyAssociations;
		}

		protected static function hasOne($associationName, $options = [], $callback = null) {

			$precedent = "className";
			$subsequent = "source";

			$through = $options["through"] ?? null;

			$as = false;
			$sourceType = false;
			if (!empty($options["as"])) {

				$as = $options["as"];
				$options["foreignKey"] = $options["foreignKey"] ?? $as."_id";

			}
			else if (!empty($through)) {

				if (!static::hasManyAssociations()->exists($through)) {
					throw new Exceptions\HasManyThroughNotFoundException($through, static::class);
				}

				$through = static::hasManyAssociations()[$through];

				$precedent = "source";
				$subsequent = "className";

			}

			$associatedClass = null;
			$associatedName = $associationName;
			if (!empty($options[$precedent])) {

				$associatedClass = NameHelper::classify(Str::camelCase($options[$precedent]), static::class, true);
				$associatedName = $options[$precedent];

			}
			else if (!empty($options[$subsequent])) {

				$associatedClass = NameHelper::classify(Str::camelCase($options[$subsequent]), static::class, true);
				$associatedName = $options[$subsequent];

			}
			else {
				$associatedClass = NameHelper::classify($associationName, static::class);
			}

			if (!class_exists($associatedClass)) {
				throw new Exception("Could not find class '$associatedClass'.", 1);
			}

			$primaryKey = $options["primaryKey"] ?? static::$primaryKey;
			$foreignKey = $options["foreignKey"] ?? Inflect::singularize(static::tableName())."_id";

			$source = $options["source"] ?? null;
			$sourceType = $options["sourceType"] ?? str_replace("App\\Models\\", "", $associatedClass);

			static::hasOneAssociations()[$associationName] = new DependentAssociation($associationName, static::class, $primaryKey, $associatedClass, $associatedName, $foreignKey, $through, $as, $source, $sourceType, $callback);

		}

		protected static function hasOneAssociations() {
			return static::associationsCache()->hasOneAssociations;
		}

		protected static function initializeAssociations() {

			if (empty(static::$_associationsCache)) {
				static::$_associationsCache = new Arrays;
			}

			if (!static::$_associationsCache->exists(static::class)) {
				static::$_associationsCache[static::class] = new AssociationStore;
			}

			return static::$_associationsCache[static::class];

		}

	}

?>