<?php

namespace Agility\Templating;

use ArrayUtils\Arrays;
use Closure;
use Exception;
use FileSystem\FileSystem;
use Phpm\Exceptions\MethodExceptions\InvalidArgumentTypeException;

	class Template {

		/* Base path inside which a template would be searched */
		private $_basePath;

		/* Exposes entire objects to the template */
		private $_exposedObject;

		function __construct($basePath, $object) {

			if (is_string($basePath)) {
				$this->_basePath = FileSystem::path(trim($basePath, "/"), true);
			}
			else if (is_a($basePath, "FileSystem\\FileSystem")) {
				$this->_basePath = clone $basePath;
			}
			else {
				throw new Exception("Templates base path should be string or an object of class FileSystem\\FileSystem.", 1);
			}

			$this->_exposedObject = $object;

		}

		function __call($method, $args = []) {
			return call_user_func_array([$this->_exposedObject, $method], $args);
		}

		function __get($attr) {
			return $this->_exposedObject->$attr;
		}

		function __set($attr, $value) {
			$this->_exposedObject->$attr = $value;
		}

		private function getTemplateName($template) {

			if (($templateName = $this->templateExists($template)) === false) {
				return $template;
			}

			return $templateName;

		}

		function load($template, $data = []) {

			if (!is_array($data)) {
				throw new InvalidArgumentTypeException("Agility\\Templating\\Template::load()", 1, "Array", gettype($data));
			}

			return $this->render($this->getTemplateName($template), $data, function($template, $data) {

				ob_start();

				if (!empty($data)) {
					extract($data);
				}

				require $template;
				$content = ob_get_clean();

				return $content;

			});

		}

		protected function render($template, $data, $callback) {
			return ($callback->bindTo($this->_exposedObject, $this->_exposedObject))($template, $data);
		}

		function templateExists($template) {

			if (($templatePath = $this->_basePath->has($template)) !== false) {
				return $templatePath;
			}
			else if (($templatePath = $this->_basePath->has($template.".php")) !== false) {
				return $templatePath;
			}
			else if (($templatePath = $this->_basePath->has($template.".php.at")) !== false) {
				return $templatePath;
			}
			else if (($templatePath = $this->_basePath->has($template.".at")) !== false) {
				return $templatePath;
			}

			return false;

		}

	}

?>