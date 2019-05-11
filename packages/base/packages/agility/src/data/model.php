<?php

namespace Agility\Data;

use AttributeHelper\Accessor;
use ArrayUtils\Arrays;
use Phpm\Nil;
use JsonSerializable;

if (!defined("nil")) {
	define ("nil", Nil::nil());
}

	class Model implements JsonSerializable {

		use Accessor;
		use Associations\Builder;
		use Callbacks\Callback;
		use Helpers\Dispatch;
		use Helpers\Initializer;
		use Helpers\Inspect;
		use Persistence\Persist;
		use Persistence\State;
		use Relations\FinderMethods;
		use Relations\QueryExtensions;
		use Relations\QueryMethods;
		use Schema\Attributes;
		use Schema\Builder;
		use Validations\Validate;

		protected static $primaryKey = "id";
		protected static $autoIncrementingPrimaryKey = true;
		protected static $touchTimeStamps = true;

		function __construct() {
			$this->_initialize();
		}

	}

?>