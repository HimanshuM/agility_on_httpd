<?php

namespace Agility\Templating;

	trait Navigation {

		protected $_breadcrumbs = [];

		function breadcrumbs($name = false, $link = false) {

			if ($name === false) {
				return $this->_breadcrumbs;
			}
			else if (is_array($name)) {
				$this->_breadcrumbs = $name;
			}
			else {
				$this->_breadcrumbs[] = ["link" => $link, "name" => $name];
			}

		}

		function breadcrumbsCount() {
			return count($this->_breadcrumbs);
		}

	}

?>