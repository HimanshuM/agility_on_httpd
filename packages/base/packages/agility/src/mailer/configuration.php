<?php

namespace Agility\Mailer;

use AttributeHelper\Accessor;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;

	class Configuration {

		use Accessor;

		protected $deliveryMethod = "mail";

		function __construct() {
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "defineDeliveryMethod");
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