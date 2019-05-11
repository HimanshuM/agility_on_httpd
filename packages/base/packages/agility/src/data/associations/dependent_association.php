<?php

namespace Agility\Data\Associations;

use Agility\Data\Cache\AssociationStore;
use Agility\Data\Relation;
use Agility\Data\Relations\Scope;
use AttributeHelper\Accessor;
use StringHelpers\Inflect;
use StringHelpers\Str;

	class DependentAssociation {

		use Accessor;

		protected $_name;

		protected $_ownerClass;
		protected $_parentObject;
		protected $_parentPrimaryKey;

		protected $_associatedClass;
		protected $_associatedName;
		protected $_associatedForeignKey;

		protected $_through;

		protected $_as = null;

		protected $_source;
		protected $_sourceType;

		protected $_callback;
		protected $_object;

		function __construct($name, $ownerClass, $primaryKey, $associatedClass, $associatedName, $associatedForeignKey, $through, $as, $source, $sourceType, $callback = null) {

			$this->_name = $name;

			$this->_ownerClass = $ownerClass;
			$this->_parentPrimaryKey = $primaryKey;

			$this->_associatedClass = $associatedClass;
			$this->_associatedName = $associatedName;
			$this->_associatedForeignKey = $associatedForeignKey;

			$this->_through = $through;

			$this->_as = $as;

			$this->_source = $source;
			$this->_sourceType = $sourceType;

			$this->_callback = $callback;

			$scope = $this->_ownerClass::getOrAddScope();
			$scope->add($this->_name, [$this, "prepare"]);

			$this->methodsAsProperties();
			$this->prependUnderscore();
			$this->readonly("through");
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "dispatcher");

		}

		protected function getRelation($fromTable) {

			if (is_a($fromTable, Scope::class)) {
				return $fromTable->getExternalObject();
			}
			else if (!empty($this->_as)) {
				return new Relation($this->_associatedClass);
			}
			else if (empty($this->_through)) {
				return new Relation($this->_associatedClass);
			}
			else {

				$farRelation = $this->_through->prepare($fromTable);
				if (is_a($farRelation, "Agility\\Data\\Relations\\Scope")) {
					$farRelation = $farRelation->getExternalObject();
				}

				return $farRelation;

			}

		}

		function prepare($owner) {

			$parentTable = $this->_ownerClass::aquaTable();
			$associatedTable = $this->_associatedClass::aquaTable();

			$parentPrimaryKey = $this->_parentPrimaryKey;
			$foreignKey = $this->_associatedForeignKey;

			$relation = $this->getRelation($owner);
			$isScope = false;

			if (!empty($this->_as)) {

				$asType = $this->_as."_type";
				$relation->where($associatedTable->$foreignKey->eq($owner->$parentPrimaryKey))->where($associatedTable->$asType->eq(str_replace("App\\Models\\", "", $this->_ownerClass)));

			}
			else if (empty($this->_through)) {

				if (is_a($owner, Scope::class)) {

					$relation
						->from($this->_associatedClass)
						->select($associatedTable->_name.".*")
						->join($parentTable->_name)
						->on($associatedTable->$foreignKey->eq($parentTable->$parentPrimaryKey));

					$isScope = true;

				}
				else {
					$relation->where($associatedTable->$foreignKey->eq($owner->$parentPrimaryKey));
				}

			}
			else {

				$throughClass = $this->_through->_associatedClass;
				$throughTable = $throughClass::aquaTable();
				$associatedThroughRelation = $this->_source ?? Inflect::singularize($this->_associatedName);

				if (!$throughClass::associationsCache()->belongsToAssociations->exists($associatedThroughRelation)) {
					throw new Exceptions\HasManyThroughSourceNotFoundException($this->_through->_associatedName, $associatedThroughRelation, $this->_ownerClass);
				}

				$throughBelongsToAssociation = $throughClass::associationsCache()->belongsToAssociations[$associatedThroughRelation];
				$throughForeignKey = Str::snakeCase($throughBelongsToAssociation->associatedForeignKey);
				$throughOwnerPrimaryKey = $throughBelongsToAssociation->primaryKey;

				$relation
					->from($this->_associatedClass)
					->select($associatedTable->_name.".*")
					->join($throughTable->_name)
					->on($throughTable->$throughForeignKey->eq($associatedTable->$throughOwnerPrimaryKey));

				if ($throughBelongsToAssociation->polymorphic) {
					$relation->where([$throughBelongsToAssociation->associatedForeignType() => $this->_sourceType]);
				}

				if (is_a($owner, Scope::class)) {

					$relation
						->join($parentTable->_name)
						->on($parentTable->$parentPrimaryKey->eq($throughTable->$foreignKey));

				}

			}

			if (!empty($this->_callback)) {
				($this->_callback->bindTo($relation, $relation))();
			}

			if ($isScope) {

				$owner->setExternalObject($relation);
				return $owner;

			}
			else {

				$scope = $this->_associatedClass::getOrAddScope();
				$scope->setExternalObject($relation);
				return $scope;

			}

		}

	}

?>