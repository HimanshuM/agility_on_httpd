<?php

namespace Agility\Data\Validations;

	class UniquenessValidator extends Base {

		function validate($object) {

			if (is_array($this->attribute)) {
				$this->validateMultiAttribute($object);
			}
			else {
				$this->validateSingleAttribute($object);
			}

		}

		protected function validateMultiAttribute($object) {

			$allSet = true;
			$where = [];
			foreach ($this->attribute as $attribute) {

				if (!$object->isSet($attribute)) {

					$allSet = false;
					break;

				}
				else {
					$where[$attribute] = $object->$attribute;
				}

			}

			if (!$allSet) {
				return;
			}

			$class = get_class($object);
			$other = $class::where($where)->first;
			if (!empty($other)) {

				if ($object->fresh || $other->valueOfPrimaryKey() != $object->valueOfPrimaryKey()) {
					$object->errors->add($attribute, $this->message ?? $object->$attribute." has already been taken");
				}

			}

		}

		protected function validateSingleAttribute($object) {

			$attribute = $this->attribute;
			if ($object->isSet($attribute)) {

				$class = get_class($object);
				if (!empty($other = $class::findBy($attribute, $object->$attribute))) {

					if ($object->fresh || $other->valueOfPrimaryKey() != $object->valueOfPrimaryKey()) {
						$object->errors->add($attribute, $this->message ?? $object->$attribute." has already been taken");
					}

				}

			}

		}

	}

?>