<?php

namespace Agility\Data\Types;

use Aqua\SqlString;

	class Raw {

		static function sql($string) {
			return new SqlString($string);
		}

	}

?>