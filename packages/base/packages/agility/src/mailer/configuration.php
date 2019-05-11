<?php

namespace Agility\Mailer;

use AttributeHelper\Accessor;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;

	class Configuration {

		use Accessor;

		protected $assetHost;
		protected $urlHost;
		protected $deliveryMethod = "mail";

		function __construct() {
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "defaultCallback");
		}

		protected function defaultCallback($name, $args = null) {

			if ($name == "deliveryMethod") {
				return $this->defineDeliveryMethod($name, $args);
			}
			elseif ($name == "assetHost") {

				if (!is_string($args)) {
					return false;
				}

				if (is_null($args)) {
					return $this->assetHost;
				}
				else {
					$this->assetHost = $args;
				}

			}
			elseif ($name == "urlHost") {

				if (!is_string($args)) {
					return false;
				}

				if (is_null($args)) {
					return $this->urlHost;
				}
				else {
					$this->urlHost = $args;
				}

			}

		}

		protected function defineDeliveryMethod($name, $value = null) {

			if (is_null($value)) {
				return $deliveryMethod;
			}

			if (!in_array($value, ["mail", "sendmail"]) && !is_a($value, Smtp::class)) {
				throw new InvalidTypeException("Configuration::mailer()->deliveryMethod", ["sendmail", "Smtp"]);
			}

			$this->deliveryMethod = $value;

		}

	}

?>