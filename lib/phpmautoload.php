<?php

	spl_autoload_register(function($class) {

		if (($conf = load_rc_file()) == false) {
			return;
		}

		$class = str_replace("\\", "/", $class);

		$matches = [];
		$package = lcfirst($class/*$components[0]*/);
		preg_match_all("/[^\/][A-Z]/", $package, $matches);
		foreach ($matches[0] as $match) {
			$package = str_replace($match, $match[0]."_".strtolower($match[1]), $package);
		}

		$package = strtolower($package);
		$components = explode("/", $package);

		if (strpos($class, "Phpm") === 0) {

			$path = substr($package, 4).".php";
			$class = realpath(__DIR__.$path);
			if (empty($class)) {
				$class = realpath(__DIR__."/../src".$path);
			}
			require_once($class);
			return;

		}

		if (($package_set_home = $conf["package_set"]) === false) {

			if (($package_set_home = getenv("PHPMP_HOME")) === false) {
				$package_set_home = realpath(__DIR__."/../packages/default/packages");
			}
			else if (($package_set_home = realpath($package_set_home)) === false) {
				return;
			}

		}
		$package_set_home = rtrim($package_set_home, "/")."/";

		$package_home = $package_set_home.$components[0];

		if (file_exists($package_home."autoload.php")) {
			require_once($package_home."autoload.php");
		}
		else if (file_exists($package_home."/src/".implode("/", array_slice($components, 1)).".php")) {
			require_once($package_home."/src/".implode("/", array_slice($components, 1)).".php");
		}

	});

	function register_testsuites_autoload($class) {

		$class = trim(str_replace("\\", "/", $class), "/");

		$matches = [];
		$package = lcfirst($class/*$components[0]*/);
		preg_match_all("/[^\/][A-Z]/", $package, $matches);
		foreach ($matches[0] as $match) {
			$package = str_replace($match, $match[0]."_".strtolower($match[1]), $package);
		}

		$package = strtolower($package);

		if (($package_set_home = getenv("PHPMP_HOME")) === false) {
			$package_set_home = realpath(__DIR__."/../packages/default/packages");
		}
		$package_set_home = rtrim($package_set_home, "/")."/";

		$test_suite = $package_set_home.$package;

		if (file_exists($test_suite.".php")) {
			require_once($test_suite.".php");
		}

	}

	function load_rc_file() {

		$conf = [
			"package_set" => false
		];

		if (file_exists(".phpmrc")) {

			$rc = file(".phpmrc", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($rc as $line) {

				$line = trim($line);
				if ($line[0] == "#") {
					continue;
				}

				$config = explode("=", $line);
				$key = trim($config[0]);
				$value = trim($config[1]);
				if ($key == "no_load") {

					if ($value == "1" || $value == "true") {
						return false;
					}

				}
				else if ($key == "package_set") {

					if (($path = realpath(__DIR__."/../packages/".$value."/packages")) === false) {
						return false;
					}

					$conf["package_set"] = $path;

				}

			}

		}

		return $conf;

	}

?>