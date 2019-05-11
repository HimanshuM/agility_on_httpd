<?php

namespace Agility\Data\Validations;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	class Validations {

		use Accessor;

		protected $validationsOnCreate;
		protected $validationsOnSave;
		protected $validationsOnUpdate;

		function __construct() {

			$this->validationsOnCreate = new Arrays;
			$this->validationsOnSave = new Arrays;
			$this->validationsOnUpdate = new Arrays;

			$this->readonly("validationsOnCreate", "validationsOnSave", "validationsOnUpdate");

		}

		function addValidation($attribute, $validator, $options) {

			$on = strtolower($options["on"] ?? "save");
			if ($on == "create") {
				$on = "Create";
			}
			else if ($on == "update") {
				$on = "Update";
			}
			else {
				$on = "Save";
			}

			$options = new Arrays($options);

			$validationType = "validationsOn".$on;

			if (($validatorClass = Base::isAvailable($validator)) !== false) {
				$this->$validationType[] = new $validatorClass($attribute, $options);
			}
			else {
				$this->$validationType[] = new Base($attribute, $options, $validator);
			}

		}

		protected function compileArgs($args) {

			$attributes = [];
			$options = [];
			foreach ($args as $arg) {

				if (is_array($arg)) {

					$arg = new Arrays($arg);
					$options = $arg;

				}
				else if (is_a($arg, Arrays::class)) {
					$options = $arg;
				}
				else {
					$attributes[] = $arg;
				}

			}

			return [$attributes, $options];

		}

		function runOnCreateValidations($object) {
			return $this->runValidationsOn("create", $object);
		}

		function runOnSaveValidations($object) {
			return $this->runValidationsOn("save", $object);
		}

		function runOnUpdateValidations($object) {
			return $this->runValidationsOn("update", $object);
		}

		function runValidationsOn($on, $object) {

			$on = ucfirst($on);
			$validationType = "validationsOn".$on;
			foreach ($this->$validationType as $validation) {
				$validation->validate($object);
			}

		}

	}

?>