<?php

namespace Agility\Mailer;

	class Email {

		public $email = "";
		public $name = "";

		function __construct($email, $name) {

			$this->email = $email;
			$this->name = $name;

		}

	}

?>