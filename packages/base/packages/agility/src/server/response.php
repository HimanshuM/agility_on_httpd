<?php

namespace Agility\Server;

use Agility\Http\Mime\MimeTypes;
use FileSystem\File;
use Phpm\Exceptions\ClassExceptions\MethodNotFoundException;

	final class Response {

		protected $response;
		public $cookies = [];
		protected $cookiesWritten = false;

		function header($key, $value) {
			header($key.": ".$value);
		}

		function redirect($location, $status = 302) {

			$this->status($status);
			header("Location: $location");

		}

		function respond($response, $status = 200) {

			if ($status != 200) {
				$this->status($status);
			}

			$this->sendCookies();
			$this->write($response);

		}

		function sendCookies() {

			if ($this->cookiesWritten) {
				return;
			}

			foreach ($this->cookies as $cookie) {
				$cookie->write($this);
			}

			$this->cookiesWritten = true;

		}

		function sendFile($file, $options = []) {

			$fileSize = "";
			$filename = $options["name"] ?? "";
			$mimeType = "";

			if (is_a($file, File::class)) {

				$fileSize = $file->size;
				$filename = $filename ?: $file->basename;

				$mimeType = $file->extension;
				$file = $file->path;

			}
			else {

				$fileSize = filesize($file);
				$filename = $filename ?: basename($file);

				$mimeType = pathinfo($this->_path)["extension"];

			}

			$contentType = $options["Content-Type"] ?? (MimeTypes::name($mimeType) ?: "application/octet-stream");

			header("HTTP/1.1 200");
			header("Content-Description: File Transfer");
			header("Content-Type: ".$contentType);
			header("Content-Disposition: attachment; filename=\"".$filename."\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate");
			header("Pragma: public");
			header("Content-Length: " . $fileSize);
			readfile($file);

			exit();

		}

		function write($response) {

			$this->sendCookies();
			echo $response;

		}

		function status($status = 200) {
			header("HTTP/1.1 ".$status);
		}

	}

?>