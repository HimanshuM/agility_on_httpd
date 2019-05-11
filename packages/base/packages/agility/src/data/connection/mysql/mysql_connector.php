<?php

namespace Agility\Data\Connection\Mysql;

use Agility\Configuration;
use Agility\Data\Connection\Base;
use Agility\Data\Connection\InvalidFetchTypeException;
use Agility\Logger\Log;
use Agility\Logger\Psr\LogLevel;
use Aqua;
use Aqua\Visitors\MysqlVisitor;
use AttributeHelper\Accessor;
use Exception;
use PDO;

	class MysqlConnector extends Base {

		use Accessor;

		protected $_instanceType;

		protected $_host;
		protected $_port;
		protected $_unixSocket;
		protected $_charSet;
		protected $_dbName;
		protected $_username;
		protected $_password;
		protected $_extraConfig;
		protected $_tablePrefix;
		protected $_tableSuffix;

		protected $_connection = null;

		const DescribeStatement = 15;

		function __construct($connectionArray, $instanceType) {

			parent::__construct($connectionArray);

			$this->_instanceType = $instanceType;
			$this->_configure($connectionArray);

			$this->attemptConnection();

			$this->readonly(
				["tablePrefix", "_tablePrefix"],
				["tableSuffix", "_tableSuffix"]
			);

			return true;

		}

		protected function attemptConnection() {

			try {
				$this->_connection = $this->_connect();
			}
			catch (Exception $e) {
				throw $e;
			}

		}

		protected function _configure($config) {

			$this->_host = $this->_setHost($config);
			$port = $this->_setPort($config);
			if ($port != "DEFAULT") {
				$this->_port = $port;
			}
			$this->_unixSocket = $this->_setUnixSocket($config);

			$this->_charSet = $this->_setCharacterSet($config);

			$this->_dbName = $this->_setDBName($config);
			$this->_username = $this->_setUsername($config);
			$this->_password = $this->_setPassword($config);

			$this->_tablePrefix = $this->_setTablePrefix($config);
			$this->_tableSuffix = $this->_setTableSuffix($config);

			$this->_extraConfig = $this->_setExtraConfig($config);

		}

		protected function _connect($noDb = false) {

			if (!empty($this->_connection)) {
				return $this->_connection;
			}

			return $this->getPdoConnection(
					$this->_setDsn(
						($noDb ? "" : $this->_dbName), $this->_host, $this->_port, $this->_unixSocket, $this->_charSet),
					$this->_username, $this->_password, $this->_extraConfig
				);

		}

		function defaultEngine() {
			return "InnoDB";
		}

		function delete($query, $params = []) {

			$sql = $query;
			if (is_a($query, "Aqua\\Visitors\\MysqlVisitor")) {

				$sql = $query->query;
				$params = $query->params;

			}

			return $this->_executeQuery($sql, $params, 1);

		}

		function exec($query, $params = []) {

			$sql = $query;
			if (is_a($query, "Aqua\\Visitors\\MysqlVisitor")) {

				$sql = $query->query;
				$params = $query->params;

			}

			return $this->_executeQuery($sql, $params, 1);

		}

		function execute($query, $params = [], $type = 0) {

			if (is_string($query)) {
				return $this->query($query, $params, $type);
			}
			else {

				$mysqlVisitor = new MysqlVisitor;
				$query->toSql($mysqlVisitor);
				if (is_a($query, Aqua\SelectStatement::class)) {
					return $this->query($mysqlVisitor, [], $type);
				}
				else if (is_a($query, Aqua\InsertStatement::class)) {
					return $this->insert($mysqlVisitor);
				}
				else if (is_a($query, Aqua\UpdateStatement::class)) {
					return $this->update($mysqlVisitor);
				}
				else if (is_a($query, Aqua\DeleteStatement::class)) {
					return $this->delete($mysqlVisitor);
				}
				else if (is_a($query, Aqua\DescribeStatement::class)) {
					return $this->query($mysqlVisitor, [], 15);
				}

			}

		}

		protected function _executeQuery($sql, $params, $type = 0) {

			$this->_logQuery($sql, $params, $type == static::DescribeStatement ? false : false);
			if ($type == static::DDLStatement) {

				try {
					$this->_connection->exec($sql);
				}
				catch (Exception $e) {

					echo $e->getMessage()."\n";
					die;

				}

				return;

			}

			$stmt = $this->_connection->prepare($sql);

			if ($type == 0 || $type == 15) {
				$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->_instanceType);
			}
			else if ($type == 4) {
				$stmt->setFetchMode(PDO::FETCH_NUM);
			}

			if (!empty($params)) {

				if (is_array($params[0])) {

					foreach ($params as $paramsSet) {
						$this->_executeQueryForParamsSet($stmt, $paramsSet);
					}

				}
				else {

					$paramsSet = [];
					foreach ($params as $param) {

						if (is_a($param, \Aqua\SqlString::class)) {
							debug_print_backtrace();die;
							$param = $param->rawQuery;
						}

						$paramsSet[] = $param;

					}

					$this->_executeQueryForParamsSet($stmt, $paramsSet);

				}

			}
			else {

				try {
					$stmt->execute();
				}
				catch(Exception $e) {
					throw MysqlException::parseException($e);
				}

			}

			$result = false;
			if ($type == 0 || $type == 15) {
				$result = $stmt->fetchAll();
			}
			else if ($type == 4) {

				if ($stmt->columnCount() == 1) {
					$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
				}
				else {
					$result = $stmt->fetchAll();
				}

			}
			else {

				$result = $stmt->rowCount();
				if ($type == 2 && $stmt->rowCount() > 0) {
					$result = $this->_connection->lastInsertId();
				}

			}

			$stmt->closeCursor();
			$stmt = null;

			return $result;

		}

		protected function _executeQueryForParamsSet($stmt, $paramsSet) {

			try {
				$stmt->execute($paramsSet);
			}
			catch(Exception $e) {
				throw MysqlException::parseException($e);
			}

		}

		private function _getConfiguration($config, $key, $errorString, $exception = true) {

			if (!isset($config[$key])) {

				if ($exception) {
					throw new MysqlConnectionException($errorString);
				}
				else {
					Logger::log($errorString);
				}

			}

			return $config[$key];

		}

		function getExceptionClass() {
			return MysqlException::class;
		}

		function getTypeMapper() {
			return new MysqlTypes;
		}

		function insert($query, $params = []) {

			$sql = $query;
			if (is_a($query, "Aqua\\Visitors\\MysqlVisitor")) {

				$sql = $query->query;
				$params = $query->params;

			}

			return $this->_executeQuery($sql, $params, 2);

		}

		protected function _logQuery($sql, $params, $return = false) {

			foreach ($params as $param) {
				$sql = preg_replace("/\?/", $this->_connection->quote($param), $sql, 1);
			}

			// Not used anymore
			if ($return) {
				return $sql;
			}

			if (!getenv("AGILITY_NO_ECHO")) {
				Log::log(LogLevel::DB, $sql);
			}

		}

		function query($query, $params = [], $type = 0) {

			$sql = $query;
			if (is_a($query, "Aqua\\Visitors\\MysqlVisitor")) {

				$sql = $query->query;
				$params = $query->params;

			}

			if ($type != 0 && $type != 4 && $type != 10 && $type != 15) {
				throw new InvalidFetchTypeException($type);
			}

			return $this->_executeQuery($sql, $params, $type);

		}

		function quote($value, $connection = null) {

			if (($nativeValue = $this->getTypeMapper()->getNativeConstant($value)) !== false) {
				return $nativeValue;
			}

			if (empty($connection)) {
				$connection = $this->_connect();
			}
			return $this->_connection->quote($value);

		}

		function resetDatabase() {

			$connection = $this->_connect(true);
			$dbName = $this->_dbName;

			$connection->exec("DROP DATABASE `$dbName`;");
			$connection->exec("CREATE DATABASE `$dbName`;");
			$connection->exec("ALTER DATABASE `$dbName` CHARACTER SET utf8 COLLATE utf8_general_ci;");

		}

		private function _setCharacterSet($config) {
			return $config["charset"] ?? null;
		}

		private function _setDBName($config) {
			return $this->_getConfiguration($config, "database", "Database name not specified.");
		}

		// If both hostname and Unix socket are specified, precedence will be given to the Unix socket
		private function _setDsn($db = null, $host = null, $port = null, $unixSocket = null, $charSet = null) {

			if (empty($unixSocket) && empty($host)) {
				throw new MysqlConnectionException("Cannot connect to Mysql database, neither host nor unix socket is specified.");
			}

			return "mysql:".(!empty($db) ? "dbname=".$db.";" : "").(!empty($unixSocket) ? "unix_socket=".$unixSocket.";" : "").(empty($unixSocket) && !empty($host) ? "host=".$host.";".(!empty($port) ? "port=".$port.";" : "") : "").(!empty($charSet) ? "charset=".$charSet : "");

		}

		private function _setExtraConfig($config) {

			$configuration = [];
			if (isset($config["config"])) {

				if (!empty($config["config"]["persistent"]) || intval($config["config"]["persistent"]) != 0) {
					$configuration[PDO::ATTR_PERSISTENT] = true;
				}

			}

			$configuration[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
			// if (Configuration::environment() == "development") {
			// 	$configuration[PDO::ATTR_ERRMODE] |= PDO::ERRMODE_WARNING;
			// }

			return $configuration;

		}

		private function _setHost($connectionConfig) {

			if (!isset($connectionConfig["host"])) {
				return "127.0.0.1";
			}

			return $connectionConfig["host"];

		}

		private function _setPassword($config) {
			return $this->_getConfiguration($config, "password", "Password not specified. Using empty password", false);
		}

		private function _setPort($config) {
			return $config["port"] ?? null;
		}

		private function _setTablePrefix($config) {
			return $config["prefix"] ?? "";
		}

		private function _setTableSuffix($config) {
			return $config["suffix"] ?? "";
		}

		private function _setUnixSocket($config) {
			return $config["unix_socket"] ?? null;
		}

		private function _setUsername($config) {
			return $this->_getConfiguration($config, "username", "Username not specified.");
		}

		function toSql($query, $params = []) {

			$sql = $query;
			if (is_string($query)) {
				return $this->_logQuery($query, $params, true);
			}
			else {

				$mysqlVisitor = new MysqlVisitor;
				$query->toSql($mysqlVisitor);

				return $this->_logQuery($mysqlVisitor->query, $mysqlVisitor->params, true);

			}

		}

		function update($query, $params = []) {

			$sql = $query;
			if (is_a($query, "Aqua\\Visitors\\MysqlVisitor")) {

				$sql = $query->query;
				$params = $query->params;

			}

			return $this->_executeQuery($sql, $params, 1);

		}

	}

?>