<?php

namespace Agility\Data\Relations;

use Agility\Data\Helpers\NameHelper;
use Agility\Data\Relation;

	/** FinderMethods
	 * Finder methods look for records in the given table base upon the criteria.
	 * If the records are found, they return the array of records;
	 * if not found, they return false.
	 */
	trait FinderMethods {

		static function all() {
			return (static::initializeRelation())->all;
		}

		static function find($id) {
			return static::findBy(static::$primaryKey, $id);
		}

		static function findBy($column, $value) {

			$column = NameHelper::getStorableName($column);

			$value = Relation::resolveSearchValue($value);
			if (!is_array($value)) {
				return static::where(static::aquaTable()->$column->eq($value))->first;
			}
			else {
				return static::where(static::aquaTable()->$column->in($value))->all;
			}

		}

		static function findByResolver($stub, $values) {

			$matches = [];
			$offset = 0;
			$attributes = [];
			if (preg_match_all("/[a-z0-9](And)[A-Z]/", $stub, $matches, PREG_OFFSET_CAPTURE)) {

				foreach ($matches[1] as $i => $match) {

					$attributeName = NameHelper::getStorableName(substr($stub, $offset, $match[1] - $offset));
					$attributes[$attributeName] = $values[$i];
					$offset = $match[1] + strlen($match[0]);

				}

				$attributes[NameHelper::getStorableName(substr($stub, $offset))] = $values[$i + 1];

			}
			else {
				$attributes[NameHelper::getStorableName($stub)] = $values[0];
			}

			$resultSet = static::where($attributes)->all;
			if ($resultSet->empty) {
				return false;
			}
			else {
				return $resultSet->first;
			}

		}

	}

?>