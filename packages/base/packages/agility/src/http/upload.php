<?php

namespace Agility\Http;

use AttributeHelper\Accessor;

	class Upload {

		use Accessor;

		protected $fieldName;
		protected $name;
		protected $tempName;
		protected $size;
		protected $error;
		protected $type;
		protected $mimeType;

		private function __construct($fieldName, $fileParams) {

			$this->fieldName = $fieldName;
			$this->name = $fileParams["name"];
			$this->tempName = $fileParams["tmp_name"];
			$this->size = $fileParams["size"];
			$this->error = $fileParams["error"];
			$type = $this->type = $fileParams["type"];

			$this->mimeType = Mime\MimeTypes::$type();

			$this->readonly("fieldName", "name", "tempName", "size", "error", "type", "mimeType");

		}

		static function constructFileArray($param, $file) {
			return [
				"name" => $file["name"]["$param"],
				"tmp_name" => $file["tmp_name"][$param],
				"size" => $file["size"][$param],
				"error" => $file["error"][$param],
				"type" => $file["type"][$param]
			];
		}

		static function prepare($fieldName, $fileParams) {
			return new Upload($fieldName, $fileParams);
		}

		static function errorCodes($error) {

			switch($error) {

				case UPLOAD_ERR_OK:
					return "There is no error, the file uploaded with success.";

				case UPLOAD_ERR_INI_SIZE:
					return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";

				case UPLOAD_ERR_FORM_SIZE:
					return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";

				case UPLOAD_ERR_PARTIAL:
					return "The uploaded file was only partially uploaded.";

				case UPLOAD_ERR_NO_FILE:
					return "No file was uploaded.";

				case UPLOAD_ERR_NO_TMP_DIR:
					return "Missing a temporary folder. Introduced in PHP 5.0.3.";

				case UPLOAD_ERR_CANT_WRITE:
					return "Failed to write file to disk. Introduced in PHP 5.1.0.";

				case UPLOAD_ERR_EXTENSION:
					return "A PHP extension stopped the file upload.";

			}

		}

		function error() {
			return Upload::errorCodes($this->error);
		}

		function moveTo($location) {
			move_uploaded_file($this->tempName, $location);
		}

	}

?>