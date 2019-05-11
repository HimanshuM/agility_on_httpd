<?php

namespace Agility\Server;

	class ContentNegotiator {

		static function buildAcceptableContentArray($acceptHeader) {

			$matches = [];
			preg_match_all("/(((\w+|\*)\/(\w+((\+\w+)|(-\w+)+)?|\*))((;[^\W\dA-Z && q]+=\d)*(,\s*((\w+|\*)\/(\w+((\+\w+)|(-\w+)+)?|\*)))*(;[^\W\dA-Z && q]+=\d)*)*)(;q+=\d(.\d)?)*/", $acceptHeader, $matches);

			$mediaTypes = $matches[1];
			$contentTypes = [];
			for ($i=0; $i < count($matches[1]); $i++) {
				$contentTypes = array_merge($contentTypes, self::parseMediaTypeString($matches[1][$i]));
			}

			return $contentTypes;

		}

		private static function parseMediaTypeString($mediaTypesString) {

			$allTypes = explode(",", $mediaTypesString);
			$ret = [];
			foreach ($allTypes as $eachType) {
				$ret[] = self::decodeMediaType($eachType);
			}
			return $ret;

		}

		private static function decodeMediaType($mediaType) {

			$mediaType = explode(";", $mediaType);
			return trim($mediaType[0]);

		}

	}

?>