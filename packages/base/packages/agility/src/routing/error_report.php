<?php

namespace Agility\Routing;

use Agility\Configuration;
use Agility\Http\Exceptions\HttpException;
use Agility\Logger\Log;
use Agility\Server\Response;
use Agility\Templating\Template;
use FileSystem\FileSystem;

	trait ErrorReport {

		protected function exceptionGetSource($e) {

			$sourceFile = $e->getFile();
			$lines = FileSystem::open($sourceFile)->lines;

			$offset = $e->getLine();
			if ($offset < 9) {
				$offset = 0;
			}
			else {
				$offset -= 9;
			}

			$slicedLines = array_slice($lines, $offset, $e->getLine() - $offset + 10, true);

			return [$sourceFile, $slicedLines, $e->getLine() - 1];

		}

		protected function reportError($exception, $die = false) {

			if (empty($this->response)) {
				$response = new Response;
			}
			else {
				$response = $this->response;
			}

			$status = 500;
			if (is_a($exception, HttpException::class)) {
				$status = $exception->httpStatus;
			}

			if (Configuration::environment() != "development") {

				Log::error($exception->getMessage(), $exception->getTrace());
				$response->respond("", $status);

			}
			else {

				$template = new Template(FileSystem::path(__DIR__."/views"), $this);
				$response->respond($template->load("500.php", ["e" => $exception]), $status);

			}

			if ($die) {
				die;
			}

		}

	}

?>