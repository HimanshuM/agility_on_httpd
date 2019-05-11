<?php

use Phpm\UnitTest;
use Agility\Routing\Routes;
use ArrayUtils\Arrays;

	class RouteTest extends UnitTest {

		function setUp() {
			$this->ast = [];
		}

		function testDrawRoutes() {

			Routes::draw(function() {

				// $this->get("courses", "courses#index");
				// $this->put("users/:user_id", "users#update");
				// $this->get("payments/:id", "payments#show", ["id" => "/\w+/"]);
				// $this->post("programs", "programs#new");
				// $this->constraints(["id" => "/\w+/"], function() {

				// 	$this->get("programs/:id", "programs#show", [], function() {

				// 		$this->get("components", "components#index");
				// 		$this->get("components/:cid", "components#show");

				// 	});

				// 	$this->get("profile", "user_profile#show", [], function() {
				// 		$this->get("photos/:id", "photos#show");
				// 	});

				// 	$this->get("enrollments/:id", "enrollments#show");


				// });

				$this->resources("programs", function() {
					$this->get("description", "programs#describe");
				});

			});

			Routes::inspect();

		}

		function testTreeBuilding() {

			$components = ["/", "programs", ":param"];

			$this->propagateLeaves($components);
			var_dump($this->ast);

		}

		function testArray() {

			$a = new Arrays;
			$a[] = "asd";
			if (!isset($a["c"])){
				$a["c"] = "basd";
			}
			var_dump($a);
		}

		protected function propagateLeaves($pathComponents) {

			$leaf = &$this->ast;
			foreach ($pathComponents as $component) {

				if (!isset($leaf[$component])) {
					$leaf[$component] = [];
				}

				$leaf = &$leaf[$component];

			}

			return $leaf;

		}

	}

?>