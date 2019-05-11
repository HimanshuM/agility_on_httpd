<?php

namespace Agility\Data\Validations;

	class NumericalityValidator extends Base {

		function validate($object) {

			$attribute = $this->attribute;
			if ($object->isSet($attribute) && !is_numeric($object->$attribute)) {
				$object->errors->add($attribute, $this->message ?? "$attribute should be numeric");
			}

		}

	}

?>