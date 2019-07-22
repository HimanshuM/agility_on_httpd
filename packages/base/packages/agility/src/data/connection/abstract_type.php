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
				if ($type["name"] == "float" || $type["name"] == "decimal" || $type["name"] == "double") {

					$precision = $precision ?: $type["precision"];
					$scale = $scale ?: $type["scale"];

					return $type["name"]."($precision, $scale)";

				}

				if ($type["name"] == "datetime" || $type["name"] == "timestamp") {
					return $type["name"].($precision ? "($precision)" : "");
				}

				return $type["name"].($limit ? "($limit)" : (!empty($type["limit"]) ? "(".$type["limit"].")" : ""));

			}

			return $type;

		}

	}

?>