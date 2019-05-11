<?php

namespace Agility\Server;

use Agility\Http\Mime\MimeTypes;

	class RequestFormat {

		protected $accept;
		protected $acceptArray = [];

		protected $preferred;

		function __construct($accept, $defaultAccept = "text/html") {

			$this->accept = $accept;

			if (is_null($this->accept)) {

				$this->acceptArray = [$defaultAccept];
				$this->preferred = $defaultAccept;

			}
			else {
				$this->buildAcceptFormat($defaultAccept);
			}

		}

		protected function buildAcceptFormat($defaultAccept) {

			$acceptableContentTypes = ContentNegotiator::buildAcceptableContentArray($this->accept);
			if (count($acceptableContentTypes) == 1) {

				if ($acceptableContentTypes[0] == "*/*") {
					array_unshift($acceptableContentTypes, $defaultAccept);
				}

			}

			foreach ($acceptableContentTypes as $i => $accept) {

				$name = "unknown";
				if (($name = MimeTypes::name($accept)) !== false){
					$this->acceptArray[$name] = $accept;
				}
				else {
					$this->acceptArray[] = $accept;
				}

				if ($i == 0) {
					$this->preferred[$name] = $accept;
				}

			}

		}

		function __get($name) {
			return isset($this->preferred[$name]);
		}

	}

?>