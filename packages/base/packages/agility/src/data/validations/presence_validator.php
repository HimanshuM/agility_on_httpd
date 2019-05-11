<?php

namespace Agility\Data\Validations;

	class PresenceValidator extends Base {

		function validate($object) {

			$attribute = $this->attribute;
			if (!$object->isSet($attribute)) {
				$object->errors->add($attribute, $this->message ?? "$attribute is not present");
			}
			else if (empty(trim($object->$attribute))) {
				$object->errors->add($attribute, $this->message ?? "$attribute cannot be empty");
			}

		}

	}

?>