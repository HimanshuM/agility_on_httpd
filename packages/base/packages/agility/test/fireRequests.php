<?php

	// sleep(5);die;

	// echo file_get_contents("http://localhost:8000")."\n";

	$i = 0;
	$errors = 1;
	while ($i < 1000) {

		$conn = new swoole_http_client("localhost", 8000);
		$conn->set([
			"timeout" => 1
		]);
		$conn->setHeaders([
			'Host' => "localhost",
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Accept-Encoding' => 'gzip',
		]);

		$conn->get("/", function($conn) use ($i, $errors) {

			$res = $conn->body;
			if (empty($res)) {
				$errors += 1;
				echo $errors;echo "###################################################################################################################\n";
			}
			else {

				if (empty($res = json_decode($res, true))) {
					$errors += 1;
					echo $errors;
					echo ($errors++)."###################################################################################################################3\n";
				}
				else {
					echo json_encode($res)."\n";
				}

			}
			$conn->close();

		});

		$i++;


	}

?>