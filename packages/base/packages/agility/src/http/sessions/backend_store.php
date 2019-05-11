<?php

namespace Agility\Http\Sessions;

	abstract class BackendStore {

		abstract function cleanup();

		abstract function readSession($sessionId);

		// Set up a timer of Config::sessionStore()->expiry duration which invokes this function.
		function setupCleanup() {

		}

		abstract function writeSession($session);

	}

?>