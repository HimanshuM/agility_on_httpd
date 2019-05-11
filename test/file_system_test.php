<?php

	$dir = FileSystem\FileSystem::path("/code/www/phpm/packages/base/packages/agility/src/commands/generators/new");
	$children = $dir->children();

	foreach ($children as $child) {

		echo $child->cwd."<br>";
		if ($child->basename == "config") {

			echo $child->basename."'s files: <br>";

			$files = $child->children();
			foreach ($files as $file) {
				echo $file->basename."<br>";
			}

			echo "<br>";

		}

	}

?>