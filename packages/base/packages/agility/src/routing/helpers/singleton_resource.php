<?php

namespace Agility\Routing\Helpers;

use StringHelpers\Inflect;

	class SingletonResource extends Resource {

		protected $singleton = true;

		function plural() {
			return Inflect::pluralize($this->path);
		}

	}

?>