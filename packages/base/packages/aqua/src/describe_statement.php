<?php

namespace Aqua;

	class DescribeStatement extends Statement {

		function __construct(Table $relation) {
			parent::__construct($relation);
		}

	}

?>