<?php

namespace Agility\Console\Commands;

use FileSystem\File;

	class HelpCommand extends Base {

		function perform($args) {

			if ($args->empty) {
				$file = File::open(__DIR__."/../../generators/templates/help/README");
			}
			else {
				$file = File::open(__DIR__."/../../generators/templates/help/".$args->first);
			}

			echo $file->read();

		}

	}

?>