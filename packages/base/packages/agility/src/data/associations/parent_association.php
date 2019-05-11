<?php

namespace Agility\Data\Associations;

use Agility\Data\Relation;
use AttributeHelper\Accessor;
use StringHelpers\Str;

	class ParentAssociation {

		use Accessor;

		protected $_associatedForeignKey;

		protected $_ownerClass;
		protected $_primaryKey;

		protected $_polymorphic;

		protected $_relation;

		function __construct($associatedForeignKey, $ownerClass, $primaryKey, $polymorphic = false) {

			$this->_associatedForeignKey = $associatedForeignKey;

			$this->_ownerClass = $ownerClass;
			$this->_primaryKey = $primaryKey;

			$this->_polymorphic = $polymorphic;

			$this->methodsAsProperties();
			$this->prependUnderScore();
			$this->readonly("associatedForeignKey", "ownerClass", "primaryKey", "polymorphic");

		}

		function associatedForeignType() {
			return Str::snakeCase($this->_ownerClass."Type");
		}

		function fetch($associatedObject) {

			$this->prepare($associatedObject);

			$foreignKey = $this->_associatedForeignKey;
			return $this->_relation->where([$this->_primaryKey => $associatedObject->$foreignKey])->first;

		}

		function prepare($associatedObject = false) {

			if (empty($this->_polymorphic)) {
				$this->_relation = new Relation($this->_ownerClass);
			}
			else {

				$associatedForeignType = $this->_ownerClass."Type";
				// $associatedForeignType = $this->associatedForeignType();
				$ownerClass = "App\\Models\\".$associatedObject->$associatedForeignType;

				$this->_relation = (new Relation($ownerClass))/*->where([$associatedForeignType => $ownerClass])*/;

			}

		}

	}

?>