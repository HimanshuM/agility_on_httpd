<?php

namespace Agility\Http;

use Agility\Config;
use Agility\Server\AbstractController;

	class ApiController extends AbstractController {

		protected $request;
		protected $response;

		protected $session;

		private $_invoked = false;
		protected $_status = 200;

		function __construct() {
			parent::__construct();
		}

		protected function conclude($response) {
			$this->json($response);
		}

		protected function concludeOnRespondedByBeforeTrigger() {
			return Config::http()->concludeOnRespondedByBeforeTrigger;
		}

		function json($data, $status = 200) {

			if (is_null($data)) {
				$data = [];
			}

			$data = is_string($data) ? $data : json_encode($data);
			$this->respond(["json" => $data], $status);

		}

		protected function prepareParams($args) {

			$this->request = $args[0];
			$this->response = $args[1];
			$this->session = $this->request->identifySession();

			$this->params->merge($this->request->params);
			$this->params->merge($this->request->getParams);
			$this->params->merge($this->request->postParams);

		}

		function respond($response, $status = 200) {

			if ($this->_responded) {
				return;
			}

			$this->_responded = true;

			if (!is_int($status)) {
				throw new Exceptions\InvalidHttpStatusException($status);
			}
			$this->response->status($status);

			if (!$this->session->empty) {

				if (!empty($cookie = $this->session->persist())) {
					$this->response->cookies[] = $cookie;
				}

			}

			if (isset($response["html"])) {

				$this->response->header("Content-Type", "text/html");
				$this->response->write($response["html"]);

			}
			else if (isset($response["json"])) {

				$this->response->header("Content-Type", "application/json");
				$this->response->write($response["json"]);

			}

		}

		function respond404($msg = []) {
			$this->respond($msg, 404);
		}

	}

?>