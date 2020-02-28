<?php

namespace Agility;

use Agility\Console\Helpers\EchoHelper;
use Agility\Data\Connection\Pool;
use Agility\Routing\Dispatch;
use Agility\Routing\ErrorReport;
use Agility\Routing\Routes;
use Agility\Server\StaticContent;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;
use Error;
use Exception;
use StringHelpers\Str;

	abstract class Application {

		use Accessor;
		use EchoHelper;
		use ErrorReport;

		protected static $_instance;
		protected $quite;
		protected $noDatabase = false;

		function __construct() {

			$this->initialize();
			static::$_instance = $this;

		}

		protected function determine404Response() {

			if (Configuration::apiOnly()) {
				Configuration::document404(false);
			}
			else {
				StaticContent::initialize();
			}

		}

		protected function executePreInitializers() {
			Initializers\PreInitializer::execute();
		}

		function executePostInitializers() {
			Initializers\PostInitializer::execute();
		}

		protected function initialize() {

			Configuration::uploadDir("storage");
			if (Configuration::documentRoot()->has("tmp")) {
				Configuration::tempDir(Configuration::documentRoot()->chdir("tmp"));
			}

		}

		function initializeApplication() {

			$this->initializeFirstStage();
			$this->initializeSecondStage();

			// This has been moved below second stage initialization because, one of the post initializers
			// defines the default DB connection, which could be used by any of the models.
			try {
				$this->prepareApplication();
			}
			catch (Exception $e) {
				$this->reportError($e, true);
			}
			catch (Error $e) {
				$this->reportError($e, true);
			}

		}

		protected function initializeComponents() {

			$this->initializeLogging();
			$this->initializeRouting();
			$this->initializeHttp();
			$this->initializeSecurity();
			$this->initializeMailer();
			$this->setupCaching();

		}

		function initializeFirstStage() {

			$this->setupApplicationAutoloader();
			$this->setupComposerAutoloader();

			try {
				$this->executePreInitializers();
			}
			catch (Exception $e) {
				$this->die($e->getMessage());
			}

			$this->determine404Response();

			try {
				$this->initializeComponents();
			}
			catch (Exception $e) {
				$this->die($e);
			}

		}

		protected function initializeHttp() {
			Http\Configuration::initialize();
		}

		protected function initializeLogging() {
			Logger\Log::initialize();
		}

		protected function initializeMailer() {
			Mailer\Base::initialize();
		}

		protected function initializeRouting() {
			Routes::initialize();
		}

		function initializeSecondStage() {

			$this->executePostInitializers();
			$this->setupSessionStoreCleanupRoutine();

		}

		protected function initializeSecurity() {
			Http\Security\Secure::initialize();
		}

		static function instance() {
			return static::$_instance;
		}

		protected function prepareApplication() {
			AppLoader::loadModels();
		}

		function run() {

			$this->initializeApplication();
			$this->listner();

		}

		protected function setupApplicationAutoloader() {

			$cwd = Configuration::documentRoot()->cwd;
			AppLoader::setupApplicationAutoloader($cwd);

		}

		protected function setupCaching() {
			Caching\Cache::initialize();
		}

		protected function setupComposerAutoloader() {

			if (($composerAutoLoader = Configuration::documentRoot()->has("vendor/autoload.php")) !== false) {
				require_once $composerAutoLoader;
			}

		}

		protected function setupSessionStoreCleanupRoutine() {

			if (Configuration::sessionStore()->cookieStore == false) {
				Configuration::sessionStore()->storage->setupCleanup();
			}

		}

		protected function listner() {

			(new Dispatch())->serve();
			Caching\Cache::runGC();

		}

	}

?>