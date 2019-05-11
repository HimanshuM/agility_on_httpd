<?php

namespace StringHelpers;

use ArrayUtils\Arrays;

	class Str {

		static function empty($string) {

			$string = trim($string);

			if (!empty($string) && !ctype_space($string)) {
				return false;
			}

			return true;

		}

		// CamelCase
		static function camelCase($string) {

			$matches = [];
			preg_match_all("/-\w|_\w/", $string, $matches);
			foreach ($matches[0] as $match) {
				$string = str_replace($match, strtoupper($match[1]), $string);
			}

			return ucfirst($string);

		}

		// First Last
		static function humanize($str) {
			return ucwords(Str::snakeCase($str, " "));
		}

		// Returns normalized name for a path
		// string_helpers/str => StringHelpers\Str
		static function normalize($path) {

			$components = new Arrays(explode("/", $path));
			$module = $components->map(function($each) {
				return Str::camelCase(lcfirst($each));
			})->implode("\\");

			return $module;

		}

		// pascalCase
		static function pascalCase($string) {

			$matches = [];
			preg_match_all("/-\w|_\w/", $string, $matches);
			foreach ($matches[0] as $match) {
				$string = str_replace($match, strtoupper($match[1]), $string);
			}

			return lcfirst($string);

		}

		// snake_case
		static function snakeCase($string, $delimiter = "_") {

			$matches = [];
			$string = lcfirst($string);
			preg_match_all("/[A-Z]/", $string, $matches);
			foreach ($matches[0] as $match) {
				$string = str_replace($match, $delimiter.strtolower($match), $string);
			}

			return $string;

		}

		// Returns storable name for a class or a namespace
		// StringHelpers\Str => string_helpers/str
		static function storable($module) {

			$components = new Arrays(explode("\\", $module));
			$path = $components->map(function($each) {
				return Str::snakeCase(lcfirst($each));
			})->implode("/");

			return $path;

		}

		static function componentName($class, $delimiter = "\\") {

			$components = explode($delimiter, $class);
			return array_pop($components);

		}

	}

?>