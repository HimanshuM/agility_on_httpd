<?php

namespace Aqua;

	trait Functions {

		function avg() {
			return (new Attribute(new NamedFunction("Average", $this), $this->table))->as("avg_".NamedFunction::$id);
		}

		function count() {
			return (new Attribute(new NamedFunction("Count", $this), $this->table))->as("count_".NamedFunction::$id);
		}

		function sum() {
			return (new Attribute(new NamedFunction("Sum", $this), $this->table))->as("sum_".NamedFunction::$id);
		}

		function max() {
			return (new Attribute(new NamedFunction("Max", $this), $this->table))->as("max_".NamedFunction::$id);
		}

		function min() {
			return (new Attribute(new NamedFunction("Min", $this), $this->table))->as("min_".NamedFunction::$id);
		}

	}

?>