<?php

namespace Agility\Http;

use Agility\Config;
use Agility\Data\Exceptions\RecordNotFoundException;
use Agility\Data\Model;
use Agility\Routing\Dispatch;
use Agility\Server\AbstractController;
use Agility\Server\Exceptions\ParameterMissingException;
use ArrayUtils\Arrays;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class ApiController extends AbstractController {

		protected $request;
		protected $response;

		protected $session;

		private $_invoked = false;
		protected $_status = 200;

		protected $jsonEncodeGrouping = "all";

		function __construct() {

			parent::__construct();
			$this->setDefaultRescuers();

		}

		protected function conclude($response) {
			$this->json($response);
		}

		protected function concludeOnRespondedByBeforeTrigger() {
			return Config::http()->concludeOnRespondedByBeforeTrigger;
		}

		protected function encodeResponse($data) {

			if (is_string($data)) {
				return $data;
			}

			if (is_a($data, Model::class)) {

				if ($this->jsonEncodeGrouping != "none") {
					$data = [$this->getModelName(get_class($data)) => $data];
				}

			}
			elseif ($this->jsonEncodeGrouping == "all") {

				if (is_a($data, Arrays::class) || is_array($data)) {

					if (!empty($data[0]) && is_a($data[0], Model::class)) {
						$data = [$this->getModelName(get_class($data[0]), true) => $data];
					}

				}

			}

			return json_encode($data);

		}

		protected function getModelName($class, $pluralize = false) {

			$class = str_replace("App\\Models\\", "", $class);
			if ($pluralize) {
				$class = Inflect::pluralize($class);
			}

			return Str::pascalCase($class);

		}

		function json($data, $status = 200) {

			if (is_null($data)) {
				$data = [];
			}

			$this->respond(["json" => $this->encodeResponse($data)], $status);

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

			if (isset($response["json"])) {

				$this->response->header("Content-Type", "application/json");
				$this->response->write($response["json"]);

			}
			else {

				$this->response->header("Content-Type", "text/html");
				$this->response->write($response["html"] ?? "");

			}

		}

		function respond404($msg = []) {
			$this->respond($msg, 404);
		}

		protected function rescueFrom($exception, $with) {
			Dispatch::rescueFrom($exception, [$this, $with]);
		}

		private function setDefaultRescuers() {

			$this->rescueFrom(RecordNotFoundException::class, function($exception, $response) {
				$response->status(404);
			});
			$this->rescueFrom(ParameterMissingException::class, function($exception, $response) {
				$response->status($exception->httpStatus);
			});

		}

	}

?>