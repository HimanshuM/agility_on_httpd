<?php

namespace Agility\Data\Validations;

use ArrayUtils\Arrays;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;
use StringHelpers\Inflect;

	class InclusionValidator extends Base {

		function __construct($attribute, $options) {

			if (!$options->exists("in")) {
				throw new Exceptions\MissingValidationAttributeException(InclusionValidator::class, "in");
			}
			if (!is_array($options["in"]) && !is_a($options["in"], Arrays::class)) {
				throw new InvalidTypeException("in", ["array"]);
			}

			$options["in"] = new Arrays($options["in"]);

			parent::__construct($attribute, $options);

		}

		function validate($object) {

			$attribute = $this->attribute;
			if ($object->isSet($attribute) && !$this->options["in"]->has($object->$attribute)) {
				$object->errors->add($attribute, $this->message ?? "$attribute must be one of ".Inflect::toSentence($this->options["in"], "or"));
			}

		}

	}

?>