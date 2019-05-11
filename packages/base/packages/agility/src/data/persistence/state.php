<?php

namespace Agility\Data\Persistence;

	trait State {

		private $_fresh;
		private $_dirty = false;
		private $_deleted = false;
		private $_persisted = false;

		function deleted() {
			return $this->_deleted;
		}

		function dirty() {
			return $this->_dirty;
		}

		function fresh() {
			return $this->_fresh;
		}

		function persisted() {
			return $this->_persisted;
		}

		function valid() {
			return $this->errors->empty;
		}

		function invalid() {
			return !$this->errors->empty;
		}

	}

?>