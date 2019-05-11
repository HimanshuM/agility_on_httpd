<?php

use Phpm\UnitTest;
use StringHelpers\Inflect;

	class InflectTest extends UnitTest {

		function testPluralizePerson() {
			$this->assertEquals("people", Inflect::pluralize("person"));
		}

		function testPluralizeStatuses() {
			$this->assertEquals("statuses", Inflect::pluralize("statuses"));
		}

		function testSingularizeStatus() {
			$this->assertEquals("status", Inflect::singularize("status"));
		}

		function dataSampleWords() {

			return [
				["people", "persone"],
				["statuses", "status"],
				["status", "status"],
				["classes", "class"],
				["class", "class"]
			];

		}

		/**
	     * @dataProvider dataSampleWords
		 */
		function testSingularize($plural, $singular) {
			$this->assertEquals($singular, Inflect::singularize($plural));
		}

	}

?>