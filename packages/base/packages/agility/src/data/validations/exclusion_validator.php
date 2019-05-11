<?php

namespace Agility\Data\Validations;

use ArrayUtils\Arrays;
use Phpm\Exceptions\TypeExceptions\InvalidTypeException;
use StringHelpers\Inflect;

	class ExclusionValidator extends Base {

		function __construct($attribute, $options) {

			if (!$options->exists("in")) {
				throw new Exceptions\MissingValidationAttributeException(ExclusionValidator::class, "in");
			}
			if (!is_array($options["in"]) && !is_a($options["in"], Arrays::class)) {
				throw new InvalidTypeException("in", ["array"]);
			}

			parent::__construct($attribute, $options);

		}

		function validate($object) {

			$attribute = $this->attribute;
			if ($object->isSet($attribute) && $this->options["in"]->has($object->$attribute)) {
				$object->errors->add($attribute, $this->message ?? "$attribute cannot be any of ".Inflect::toSentence($this->options["in"], "or"));
			}

		}

	}

?>