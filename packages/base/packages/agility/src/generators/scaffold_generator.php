<?php

namespace Agility\Generators;

use Agility\Data\Generators\ModelGenerator;

	class ScaffoldGenerator extends Base {

		protected function _generate() {

			ControllerGenerator::start($this->_appPath, $this->_appRoot, clone $this->_args, true);
			ModelGenerator::start($this->_appPath, $this->_appRoot, $this->_args);

		}

		protected function _publish($template, $name, $data) {

		}

		static function start($appPath, $root, $args) {
			(new static($appPath, $root, $args, "controller"))->_generate();
		}

	}

?>