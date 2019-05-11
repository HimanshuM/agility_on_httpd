<?php

namespace Agility\Data\Types;

use AttributeHelper\Accessor;
use Phpm\Exceptions\MethodExceptions\InsufficientParametersException;
use StringHelpers\Inflect;

	class Reference extends Base {

		use Accessor;

		protected $polymorphic = false;
		protected $foreignKey = false;

		protected $onUpdate;
		protected $onDelete;

		function __construct($type) {

			parent::__construct();
			if ($type == "polymorphic") {
				$this->polymorphic = true;
			}
			else {
				$this->foreignKey = Inflect::pluralize($type);
			}

			$this->readonly("foreignKey", "polymorphic", "onUpdate", "onDelete");

		}

		function cast($value) {

			if (is_null($value)) {
				return $value;
			}

			return $value;

		}

		function nativeType($typeMapper) {

			if ($this->foreignKey) {
				return $typeMapper->getNativeType("uint");
			}

			if (func_num_args() == 1) {
				throw new InsufficientParametersException("Agility\\Data\\Types\\Reference::nativeType", 2, 1);
			}

			$name = func_get_arg(1);
			return [$name."_type" => $typeMapper->getNativeType("string"), $name."_id" => $typeMapper->getNativeType("uint")];

		}

		function options() {

			$option = [];
			if ($this->polymorphic) {
				$option["polymorphic"] = "true";
			}
			else {
				$option["foreignKey"] = "\"".$this->foreignKey."\"";
			}

			return $option;

		}

		function serialize($value) {

			if (is_null($value)) {
				return $value;
			}

			return $value;

		}

		function setParameters($params = []) {

			parent::setParameters($params);

			if (!empty($params["polymorphic"])) {
				$this->polymorphic = true;
			}
			else if (!empty($params["foreignKey"])) {

				$this->foreignKey = $params["foreignKey"];
				$this->onUpdate = $params["onUpdate"] ?? 1;
				$this->onDelete = $params["onDelete"] ?? 1;

			}

		}

		function __toString() {
			return "reference";
		}

	}

?>