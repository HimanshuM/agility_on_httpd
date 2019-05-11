<?php

namespace Agility\Mailer\Exceptions;

use Exception;

	class InsufficientMailerDataException extends Exception {

		function __construct($invalids) {
			parent::__construct("To send a mail ".Inflect::toSentence($invalids)." cannot be empty");
		}

	}

?>