<?php

namespace Agility\Console\Commands;

use Agility\Data\Generators\MigrationGenerator;
use Agility\Data\Generators\ModelGenerator;
use Agility\Generators\ControllerGenerator;
use Agility\Generators\MailerGenerator;
use Agility\Generators\ScaffoldGenerator;
use Agility\Generators\TaskGenerator;
use Agility\Initializers\ApplicationInitializer;
use ArrayUtils\Arrays;
use FileSystem\Dir;
use FileSystem\File;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class GenerateCommand extends Base {

		use ApplicationInitializer;

		private $_quite = false;
		private $_overwrite = false;
		private $_options;

		protected static $shorthand = [
			"c" => "controller",
			"m" => "model",
			"s" => "scaffold",
			"t" => "task"
		];

		protected static $generators = [
			"controller" => ControllerGenerator::class,
			"mailer" => MailerGenerator::class,
			"migration" => MigrationGenerator::class,
			"model" => ModelGenerator::class,
			"scaffold" => ScaffoldGenerator::class,
			"task" => TaskGenerator::class
		];

		static function register($stub, $className, $shorthand = false) {

			if (!empty(GenerateCommand::$shorthand[$shorthand])) {
				throw new Exceptions\GeneratorStubTakenException($shorthand);
			}

			GenerateCommand::$generators[$stub] = $className;
			GenerateCommand::$shorthand[$shorthand] = $stub;

		}

		private function _echo($str) {

			if (!$this->_quite) {
				Cli::echo($str."\n");
			}

		}

		private function _invalidCommand($stub) {

			echo "Invalid stub '$stub'.\n";
			$this->_invokeHelp("generate/help");

		}

		private function _invokeHelp($help) {

			$help = new HelpCommand;
			$help->perform($help);

		}

		function perform($args) {

			if (!$this->requireApp()) {
				return;
			}

			// $this->_parseOptions($args);

			$stub = $args->shift();
			$stub = strtolower($stub);

			if (isset(GenerateCommand::$shorthand[$stub])) {
				$stub = GenerateCommand::$shorthand[$stub];
			}

			if (!empty(GenerateCommand::$generators[$stub])) {

				if ($this->requireArgs($args, $stub)) {

					$this->instantiateApplication([]);
					$className = GenerateCommand::$generators[$stub];
					$className::start($this->_appPath, $this->_appRoot, $args);

				}

			}
			else {
				$this->_invalidCommand($stub);
			}

		}

		private function requireArgs($args, $help) {

			if ($args->empty) {

				$this->_invokeHelp($help);
				return false;

			}

			return true;

		}

	}

?>