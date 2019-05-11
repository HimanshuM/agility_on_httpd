<?php

namespace Agility\Data\Validations;

	class ConfirmationValidator extends Base {

		function __construct($attribute, $options) {

			if (!$options->exists("confirmation")) {
				$options["confirmation"] = $attribute."Confirmation";
			}

			parent::__construct($attribute, $options);

		}

		function validate($object) {

			$attribute = $this->attribute;
			$attributeConfirmation = $this->options["confirmation"];

			if ($object->isSet($attribute) && (!$object->isSet($attributeConfirmation) || $object->$attribute != $object->$attributeConfirmation)) {
				$object->errors->add($attribute, $this->message ?? "$attribute does not match confirmation");
			}

		}

	}

?>