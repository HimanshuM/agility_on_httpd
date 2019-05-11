<?php

	$start = microtime(true);
	passthru("php fireRequests.php");
	$end = microtime(true);

	echo ($end - $start);
	echo "\n";

?>