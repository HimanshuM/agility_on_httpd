<?php

namespace Agility\Http;

use Agility\Config;
use Agility\Server\StaticContent;
use AttributeHelper\Accessor;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;

	class Configuration {

		use Accessor;

		protected $concludeOnRespondedByBeforeTrigger = true;
		public $csrfTokenName = "authenticity_token";

		function __construct() {
			$this->methodsAsProperties();
		}

		static function initialize() {

			Config::sessionStore(new Sessions\Configuration);
			Config::forceSsl(false);

			Config::http(new Configuration);

			StaticContent::setEmbedablesPath();

			Mime\MimeTypes::initialize();

		}

		function concludeOnRespondedByBeforeTrigger($value = null) {

			if ($value === null) {
				return $this->concludeOnRespondedByBeforeTrigger;
			}

			if (!is_bool($value)) {
				throw new InvalidTypeException("Config::http()->concludeOnRespondedByBeforeTrigger", "boolean");
			}

			$this->concludeOnRespondedByBeforeTrigger = $value;

		}

	}

?>