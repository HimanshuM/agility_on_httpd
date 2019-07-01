<?php

namespace Agility\Server;

use Agility\Configuration;

	final class StaticContent {

		static function initialize() {

			if (is_null(Configuration::document404())) {

				if (($file404 = Configuration::documentRoot()->has("public/404.html"))) {
					Configuration::document404($file404);
				}
				else {
					Configuration::document404(false);
				}

			}

		}

		static function setEmbedablesPath() {

			Configuration::cssPath("/css/", function($setting, $value) {
				trim($value, "/")."/";
			});

			Configuration::jsPath("/js/", function($setting, $value) {
				return trim($value, "/")."/";
			});

		}

	}

?>