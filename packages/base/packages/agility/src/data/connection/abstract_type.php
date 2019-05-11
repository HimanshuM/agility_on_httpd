<?php

namespace Agility\Data\Connection;

use Agility\Data\Schema\ForeignKeyRelation;

	abstract class AbstractType {

		abstract function compileForeignKey(ForeignKeyRelation $relation);

		function getNativeConstant($constantName) {
			return static::NativeConstants[$constantName] ?? false;
		}

		function getNativeType($type, $limit = null, $precision = null, $scale = null) {

			if (!empty(static::NativeTypes[$type])) {

				$type = static::NativeTypes[$type];
				if ($type == "float" || $type == "decimal") {

					$precision = $precision ?: $type["precision"];
					$scale = $scale ?: $type["scale"];

					return $type["name"]."($precision, $scale)";

				}

				if ($type == "datetime" || $type == "timestamp") {
					return $type["name"].($precision ? "($precision)" : "");
				}

				return $type["name"].($limit ? "($limit)" : (!empty($type["limit"]) ? "(".$type["limit"].")" : ""));

			}

			return $type;

		}

	}

?>