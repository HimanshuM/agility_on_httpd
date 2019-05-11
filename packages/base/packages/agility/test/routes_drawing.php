<?php

use Phpm\UnitTest;
use Agility\Routing\Dispatch;
use Agility\Routing\Routes;
use Agility\Server\Request;
use ArrayUtils\Arrays;

	class RoutesDrawing extends UnitTest {

		function setUp() {

			Routes::draw(function() {

				// $this->get("courses", "courses#index");
				// $this->get("courses/:id", "courses#show");
				// $this->patch("users/:user_id", "users#update");
				// $this->get("payments/:id", "payments#show", ["id" => "/\w+/"]);
				// $this->post("programs", "programs#create");
				// $this->constraints(["id" => "/[A-Za-z]+/"], function() {

				// 	$this->get("enrollments/:id/edit", "enrollments#edit");

				// 	$this->get("programs/:id", "programs#show", function() {

				// 		$this->get("components", "components#index");
				// 		$this->get("components/:cid", "components#show");

				// 	});

				// 	$this->get("profile", "user_profile#show", function() {
				// 		$this->get("photos/:id", "photos#show");
				// 	});

				// });

				// $this->resources("classes", function() {
				// 	$this->get("description", "classes#describe");
				// });

				// $this->controller("payments", ["path" => "payments"], function() {

				// 	$this->get(":id/payee", "payee");
				// 	$this->post("", "create");

				// });

				// $this->scope(["controller" => "installments", "path" => "installments"], function() {
				// 	$this->get("all", "index");
				// });

				// $this->namespace("admin", ["path" => "sudo"], function() {
				// 	$this->get("courses", "courses#index");
				// 	$this->resources("videos", "topics", "students");
				// });

				// $this->scope(["namespace" => "admin"], function() {
				// 	$this->get("batches", "batches#index");
				// });

				$this->get("desc", function() {
					return "Yay";
				});

			});

			Routes::inspect("get");

		}

		function sampleRoutes() {

			return [
				["get", "courses/1", "courses#show"],
				["get", "enrollments/4/edit", "404"],
				["get", "enrollments/wer/edit", "enrollments#edit"],
				["patch", "users/3", "users#update"],
				["post", "programs", "programs#create"],
				["delete", "programs", "404"],
				["post", "payments", "payments#create"],
				["get", "payments/123", "payments#show"],
				["get", "payments/1000/payee", "payments#payee"],
				["get", "installments/all", "installments#index"],
				["get", "sudo/courses", "courses#index"],
				["get", "batches", "batches#index"],
				["delete", "sudo/videos/123", "videos#delete"],
				["get", "programs/qwe/components/123", "components#show"],
				["get", "classes/123/description", "classes#describe"],
				["post", "sudo/topics", "topics#create"],
				["patch", "sudo/students/123", "students#update"]
			];

		}

		/**
	     * @dataProvider sampleRoutes
		 */
		function testDraw($verb, $uri, $handler) {

			$request = new Request(new DummyRequest($verb, $uri));
			$dispatch = new Dispatch($request);
			$route = $dispatch->serve();
			if (is_string($route)) {
				$this->assertEquals($handler, $route);
			}
			else {
				$this->assertEquals($route->namespace.$handler, $route->namespace.$route->controller."#".$route->action);
			}

		}

	}

	class DummyRequest {

		public $server;

		function __construct($verb, $route) {

			$this->server["request_method"] = $verb;
			$this->server["path_info"] = $route;
			$this->server["remote_addr"] = "127.0.0.1";

		}

	}

?>