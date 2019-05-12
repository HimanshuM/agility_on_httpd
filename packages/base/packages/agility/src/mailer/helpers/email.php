<?php

namespace Agility\Mailer\Helpers;

	class Email {

		public $email = "";
		public $name = "";

		const Mapper = [
			"from" => "setFrom",
			"to" => "addAddress",
			"replyTo" => "addReplyTo",
			"cc" => "addCC",
			"bcc" => "addBCC"
		];

		function __construct($email, $name) {

			$this->email = $email;
			$this->name = $name;

		}

		function fill($phpMailer, $type) {

			$functionName = Email::Mapper[$type];
			if (empty($this->name)) {
				$this->name = "";
			}

			$phpMailer->$functionName($this->email, $this->name);

		}

	}

?>