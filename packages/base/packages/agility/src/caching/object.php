<?php

namespace Agility\Caching;

use DateTime;

	class Object {

		protected $_name;
		protected $_value;
		protected $_createdAt;
		protected $_lastAccessedAt;
		protected $_ttl;

		function __construct($name, $value, $ttl = -1) {

			$this->_name = $name;
			$this->_value = $value;
			$this->_createdAt = time();
			$this->_lastAccessedAt = time();
			$this->_ttl = $ttl;

		}

		function access($value = nil) {

			$this->_lastAccessedAt = time();

			if ($value !== nil) {
				$this->_value = $value;
			}

			return $this->_value;

		}

		function decr() {

			if (!is_numeric($this->_value)) {
				throw new InvalidOperationException("decr", "Cache::".$this->_name, "numeric");
			}

			return --$this->_value;

		}

		function decrBy($count = 1) {

			if (!is_numeric($this->_value)) {
				throw new InvalidOperationException("decrBy", "Cache::".$this->_name, "numeric");
			}

			return $this->_value -= $count;

		}

		function destroy($timestamp) {

			if ($this->_ttl > -1) {

				if (time() - $this->_createdAt > $this->_ttl) {

					$this->_value = null;
					return true;

				}

			}
			else if ((time() - $this->_lastAccessedAt) > $timestamp) {

				$this->_value = null;
				return true;

			}

			return false;

		}

		function incr() {

			if (!is_numeric($this->_value)) {
				throw new InvalidOperationException("incr", "Cache::".$this->_name, "numeric");
			}

			return ++$this->_value;

		}

		function incrBy($count = 1) {

			if (!is_numeric($this->_value)) {
				throw new InvalidOperationException("incrBy", "Cache::".$this->_name, "numeric");
			}

			return $this->_value += $count;

		}

		function pop($value) {

			$this->_lastAccessedAt = time();
			if (!is_array($this->_value)) {
				throw new InvalidOperationException("pop", "Cache::".$this->_name, "array");
			}

			return array_pop($this->_value);

		}

		function push($value) {

			$this->_lastAccessedAt = time();

			if (!is_array($this->_value)) {

				if (!empty($this->_value)) {
					$this->_value = [$this->_value];
				}
				else {
					$this->_value = [];
				}

			}

			$this->_value[] = $value;

		}

	}

?>