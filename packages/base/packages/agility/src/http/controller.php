<?php

namespace Agility\Http;

use Agility\Config;
use Agility\Data\Model;
use Agility\Routing\Routes;
use Agility\Templating\Render;
use Closure;

	class Controller extends ApiController {

		use Render;

		const CsrfEncryptionMethod = "aes-128-gcm";

		protected $request;
		protected $response;

		protected $forgeryProtection = false;

		private $_invoked = false;

		function __construct() {

			parent::__construct();

			$this->beforeAction("validateAuthenticityToken");

			$this->initializeTemplating();
			$this->forgeryProtection = Config::security()->forgeryProtection;

		}

		protected function conclude($response) {

			if (!$this->_responded) {

				if (empty($this->_content)) {

					$this->render(["data" => $response]);
					$this->respond(["html" => $this->_content]);

				}
				else {
					$this->respond(["html" => $this->_content]);
				}

			}

		}

		protected function cookie($key, $value = "", $expire = 0, $path = "/", $domain  = "", $secure = null, $httponly = false) {
			$this->response->cookies[] = new Cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
		}

		function csrfMetaTag() {

			if (!$this->forgeryProtection) {
				return;
			}

			if (empty(Config::http()->csrfTokenName)) {
				throw new Security\CsrfTokenNameEmptyException;
			}

			$authenticityToken = $this->formAuthenticityToken();

			$this->tag("meta", ["name" => "csrf-param", "content" => Config::http()->csrfTokenName]);
			$this->tag("meta", ["name" => "csrf-token", "content" => $authenticityToken]);

		}

		function formAuthenticityToken() {

			$this->protectFromForgery();

			if ($this->session->exists("csrfToken")) {
				return $this->session["csrfToken"];
			}

			$authenticityToken = Security\Secure::secureEncode(Security\Secure::randomBytes(Controller::CsrfEncryptionMethod), Controller::CsrfEncryptionMethod, Config::security()->encryptionKey);
			return $this->session["csrfToken"] = $authenticityToken;

		}

		protected function protectFromForgery($flag = true) {
			$this->forgeryProtection = $flag;
		}

		function redirectTo($location, $status = 302) {

			if (is_string($location)) {
				$this->redirectToLocation($location, $status);
			}
			else if (is_a($location, Model::class)) {

				$location = Routes::findRouteForModel($location);
				$this->redirectToLocation($location, $status);

			}
			else {
				throw new Exceptions\InvalidHttpLocationException($location, true);
			}

		}

		function redirectToLocation($location, $status = 302) {

			if (!is_string($location)) {
				throw new Exceptions\InvalidHttpLocationException($location);
			}

			if (strpos($location, "http") !== 0) {
				$location = "/".trim($location, "/ ");
			}

			$this->_responded = true;

			if (!is_int($status)) {
				throw new Exceptions\InvalidHttpStatusException($status);
			}

			$this->response->redirect($location, $status);

		}

		function respond404() {
			parent::respond404(["html" => file_get_contents("404.html")]);
		}

		protected function validateAuthenticityToken() {

			if (!$this->forgeryProtection || $this->request->get || $this->request->options) {
				return;
			}

			if ($this->session->exists("csrfToken")) {

				$token = "";
				if ($this->params->exists(Config::http()->csrfTokenName)) {
					$token = $this->params[Config::http()->csrfTokenName];
				}
				else {
					throw new Security\InvalidAuthenticityTokenException(static::class, $this->methodInvoked);
				}

				if ($token != $this->session["csrfToken"]) {
					throw new Security\InvalidAuthenticityTokenException(static::class, $this->methodInvoked);
				}

			}

		}

	}

?>