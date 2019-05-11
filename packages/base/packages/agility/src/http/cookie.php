<?php

namespace Agility\Http;

use Agility\Configuration;
use AttributeHelper\Accessor;

	class Cookie {

		use Accessor;

		public $key;
		public $value = "";
		public $expire = 0;
		public $path = "/";
		public $domain  = "";
		public $secure = false;
		public $httponly = false;

		function __construct($key, $value = "", $expire = 0, $path = "/", $domain  = "", $secure = null, $httponly = false) {

			$this->key = $key;
			$this->value = $value;
			$this->expire = $expire;
			$this->path = $path;
			$this->domain = $domain;
			$this->secure = $secure ?? Configuration::forceSsl() ?? false;
			$this->httponly = $httponly;

			$this->methodsAsProperties();

		}

		function write($response) {
			setcookie($this->key, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
		}

	}

?>