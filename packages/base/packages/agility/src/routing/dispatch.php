<?php

namespace Agility\Routing;

use Agility\Configuration;
use Agility\Logger\Log;
use Agility\Server\Request;
use Agility\Server\Response;
use Agility\Server\StaticContent;
use Closure;
use Error;
use Exception;
use StringHelpers\Str;

	class Dispatch {

		use ErrorReport;

		// protected $ast;
		protected $domains;
		protected $request;
		protected $response;
		protected $params;

		function __construct() {

			$this->request = new Request();
			$this->response = new Response();

			// $this->ast = $this->ast();
			$this->domains = Routes::domains();

		}

		// protected function ast() {
		// 	return Routes::ast();
		// }

		protected function astFor($domain) {

			if ($this->domains->exists($domain)) {
				return $this->domains[$domain];
			}

			return $this->domains["/"];

		}

		protected function findHandler($domain, $verb, $path) {

			$ast = $this->astFor($domain);
			return $ast->$verb->crawl($ast->$verb->pathComponents($path), true);

		}

		protected function execute($route) {

			list($controller, $action, $actionName) = $this->prepareHandler($route);
			if ($controller === false) {
				return;
			}

			$this->printHandler($controller, $actionName);
			$this->invokeHandler($controller, $action);

		}

		protected function invokeHandler($controller, $action) {

			try {
				$controller = $controller::invoke($action, [$this->request, $this->response]);
			}
			catch (Exception $e) {
				$this->reportError($e);
			}
			catch (Error $e) {
				$this->reportError($e);
			}

			$controller = null;

		}

		protected function populateParameters($route, $params) {

			if (!empty($route->parameters)) {

				foreach ($route->parameters as $i => $param) {

					if (!$this->validateParameter($route, $params, $i, $param)) {
						return false;
					}

					$this->request->addParameter($param, $params[$i]);

				}

			}

			return true;

		}

		protected function prepareControllerName($controller) {
			return Str::camelCase($controller)."Controller";
		}

		protected function prepareHandler($route) {

			$controller = "\\Agility\\Http\\Controller";
			if (Configuration::apiOnly()) {
				$controller = "\\Agility\\Http\\ApiController";
			}

			if (!empty($route->controller)) {
				$controller = $route->namespace.$this->prepareControllerName($route->controller);
			}

			if (!class_exists($controller)) {

				$this->reportError(new Exceptions\ControllerNotFoundException($controller));
				return [false, false, false];

			}

			$actionName = $route->action;
			if (is_a($route->action, Closure::class)) {
				$actionName = "Closure";
			}

			return [$controller, $route->action, $actionName];

		}

		protected function printHandler($controller, $action) {
			Log::info("Invoking ".$controller."::".$action."()");
		}

		protected function printRequest() {
			Log::info("Started ".strtoupper($this->request->method)." \"".$this->request->uri."\" for ".$this->request->ip." at ".date("Y-m-d H:i:s"));
		}

		protected function process404($route, $domain = "/") {

			list($route404, $params) = $this->findHandler($domain, "get", "404");
			if ($route404 === false) {

				if ($domain != "/") {
					return $this->process404($route);
				}

				if (!empty($file404 = Configuration::document404())) {

					Log::info("Redirecting to 404.html");
					$this->response->redirect("/".$file404);

				}
				else {

					Log::info("Responding with HTTP/1.1 404");
					$this->response->status(404);
					$this->response->respond("");

				}

			}
			else {

				Log::info("Invoking 404 handler");
				$this->request->addParameter("route", $route);
				$this->execute($route404);

			}

		}

		function serve() {

			$this->printRequest();

			list($route, $params) = $this->findHandler($this->request->host, $this->request->method, $this->request->uri);
			if ($route === false) {
				return $this->process404($route, $this->request->host);
			}

			if (!$this->validateRequest($route, $params)) {
				return $this->process404($route, $this->request->host);
			}

			$this->request->compileAcceptHeader($route->defaults["format"] ?? "text/html");

			$this->execute($route);

			gc_collect_cycles();

		}

		protected function validateDomainAndIp($route) {

			if (!empty($route->constraints["domain"])) {

				$domain = is_array($route->constraints["domain"]) ? $route->constraints["domain"] : [$route->constraints["domain"]];
				if (!in_array($this->request->host, $domain)) {
					return false;
				}

			}

			if (!empty($route->constraints["ip"])) {

				$ip = is_array($route->constraints["ip"]) ? $route->constraints["ip"] : [$route->constraints["ip"]];
				if (!in_array($this->request->ip, $ip)) {
					return false;
				}

			}

			return true;

		}

		protected function validateRequest($route, $params) {

			if (!$this->validateDomainAndIp($route)) {
				return false;
			}

			if (!$this->populateParameters($route, $params)) {
				return false;
			}

			return true;

		}

		protected function validateParameter($route, $params, $i, $param) {

			if (!$route->constraints->exists($param) || preg_match($route->constraints[$param], $params[$i]) == 1) {
				return true;
			}

			return false;

		}

	}

?>