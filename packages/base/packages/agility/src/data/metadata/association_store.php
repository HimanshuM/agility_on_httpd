<?php

namespace Agility\Data\Metadata;

use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	final class AssociationStore {

		use Accessor;

		private $belongsToAssociations;
		private $hasManyAssociations;
		private $hasAndBelongsToManyAssociations;
		private $hasOneAssociations;
		private $polymorphicAssociations;

		function __construct() {

			$this->belongsToAssociations = new Arrays;
			$this->hasManyAssociations = new Arrays;
			$this->hasAndBelongsToManyAssociations = new Arrays;
			$this->hasOneAssociations = new Arrays;
			$this->polymorphicAssociations = new Arrays;

			$this->readonly("belongsToAssociations", "hasManyAssociations", "hasAndBelongsToManyAssociations", "hasOneAssociations", "polymorphicAssociations");

		}

	}

?>