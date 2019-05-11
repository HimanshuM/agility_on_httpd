<?php

namespace Agility\Logger\Psr;

use Agility\Logger\PsrImplementor;
use Psr\Log\LoggerInterface;

	class Log implements LoggerInterface {

		protected $quite;

		use PsrImplementor;

	}

?>