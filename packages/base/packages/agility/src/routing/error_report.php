<?php

namespace Agility\Routing;

use Agility\Configuration;
use Agility\Server\Response;
use Agility\Templating\Template;
use FileSystem\FileSystem;

	trait ErrorReport {

		protected function exceptionGetSource($e) {

			$sourceFile = $e->getFile();
			$lines = FileSystem::open($sourceFile)->lines;

			$offset = $e->getLine();
			if ($offset < 5) {
				$offset = 0;
			}
			else {
				$offset -= 5;
			}

			$slicedLines = array_slice($lines, $offset, $e->getLine() - $offset + 10, true);

			return [$sourceFile, $slicedLines, $e->getLine()];

		}

		protected function reportError($exception, $die = false) {

			if (Configuration::environment() != "development") {
				return Log::error($e->getMessage(), $e->getTrace());
			}

			if (empty($this->response)) {
				$response = new Response;
			}
			else {
				$response = $this->response;
			}

			$template = new Template(FileSystem::path(__DIR__."/views"), $this);
			$response->respond($template->load("500.php", ["e" => $exception]), 500);

			if ($die) {
				die;
			}

		}

	}

?>