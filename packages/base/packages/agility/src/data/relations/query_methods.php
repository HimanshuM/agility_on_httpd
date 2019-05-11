<?php

namespace Agility\Data\Relations;

	trait QueryMethods {

		static function execute() {

			$args = func_get_args();
			if (count($args) == 0) {
				return false;
			}

			$sql = $args[0];
			$params = array_slice($args, 1);

			return static::connection()->exec($sql, $params);

		}

		static function fullJoin($table) {
			return static::join($table, "FullJoin");
		}

		static function groupBy() {
			return call_user_func_array([static::initializeRelation(), "groupBy"], func_get_args());
		}

		static function includes($table) {
			return static::initializeRelation()->includes($table);
		}

		static function innerJoin($table) {
			return static::join($table);
		}

		static function join($table, $join = "InnerJoin") {
			return static::initializeRelation()->join($table, $join);
		}

		static function leftJoin($table) {
			return static::join($table, "LeftJoin");
		}

		static function orderBy() {
			return call_user_func_array([static::initializeRelation(), "orderBy"], func_get_args());
		}

		static function pluck() {
			return call_user_func_array([static::initializeRelation(), "pluck"], func_get_args());
		}

		static function query() {

			$args = func_get_args();
			if (count($args) == 0) {
				return false;
			}

			$sql = $args[0];
			$params = array_slice($args, 1);

			return static::connection()->query($sql, $params);

		}

		static function select() {
			return call_user_func_array([static::initializeRelation(), "select"], func_get_args());
		}

		static function skip($offset) {
			return static::initializeRelation()->skip($offset);
		}

		static function take($length) {
			return static::initializeRelation()->take($length);
		}

		static function where($clause, $params = []) {
			return static::initializeRelation()->where($clause, $params);
		}

	}

?>