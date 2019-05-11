<?php

namespace Agility\Data\Validations;

	class LengthValidator extends Base {

		function __construct($attribute, $options) {

			if (!$options->exists("length")) {
				throw new Exceptions\MissingValidationAttributeException(LengthValidator::class, "length");
			}

			parent::__construct($attribute, $options);

		}

		function validate($object) {

			$attribute = $this->attribute;
			if ($object->isSet($attribute)) && strlen($object->$attribute) < $this->options["length"]) {
				$object->errors->add($attribute, $this->message ?? "$attribute should have a length of at least ".$this->options["length"]);
			}

		}

	}

?>