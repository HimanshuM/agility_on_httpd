<?php

	$pid = pcntl_fork();
	if ($pid == -1) {
		die("Could not launch background jobs");
	}
	else if ($pid) {

		if (posix_setsid() < 0) {
			die("Could not launch daemon process");
		}

		$pool = Pool::initialize();

	}
	else {

	}

?>