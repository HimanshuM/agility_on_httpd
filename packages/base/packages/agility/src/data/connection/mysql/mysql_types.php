<?php

namespace Agility\Data\Connection\Mysql;

use Agility\Data\Connection\AbstractType;
use Agility\Data\Connection\SqlTypeLengthException;
use Agility\Data\Schema\ForeignKeyRelation;

	class MysqlTypes extends AbstractType {

		const ForeignKeyModifiers = [
			"NO ACTION",
			"RESTRICT",
			"SET NULL",
			"CASCADE"
		];

		const NativeConstants = [
			"CURRENT_TIMESTAMP" => "CURRENT_TIMESTAMP",
			"NULL" => "NULL"
		];

		const NativeTypes = [
			"binary"	=> 	["name" => "blob", 			"limit" => "",	 	"regex" => '/blob(\(\d+\))?/'],
			"boolean"	=> 	["name" => "tinyint", 		"limit" => 1, 		"regex" => '/tinyint/'],
			"datetime"	=> 	["name" => "datetime", 		"precision" => 0, 	"regex" => '/datetime(\(\d+\))?/'],
			"date"		=> 	["name" => "date", 			"limit" => "",	 	"regex" => '/date/'],
			"double"	=> 	["name" => "double", 		"precision" => 10, 	"regex" => '/double(\(\d+(,\d+)?\))?/',		"scale" => 0],
			"enum"		=> 	["name" => "enum", 			"limit" => "",	 	"regex" => '/enum(\([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(,[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\))?/'],
			"float"		=> 	["name" => "float", 		"precision" => 10, 	"regex" => '/float(\(\d+(,\d+)?\))?/',		"scale" => 2],
			"integer"	=> 	["name" => "int", 			"limit" => 11, 		"regex" => '/int(\(\d+\))/'],
			"integer"	=> 	["name" => "int", 			"limit" => 11, 		"regex" => '/smallint(\(\d+\))/'],
			"integer"	=> 	["name" => "int", 			"limit" => 11, 		"regex" => '/mediumint(\(\d+\))/'],
			"integer"	=> 	["name" => "int", 			"limit" => 11, 		"regex" => '/bigint(\(\d+\))/'],
			"json"		=>	["name" => "json",			"limit" => "",		"regex" => '/json/'],
			"string"	=> 	["name" => "varchar", 		"limit" => 255, 	"regex" => '/varchar(\(\d+\))/'],
			"text"		=> 	["name" => "text", 			"limit" => 65535, 	"regex" => '/text(\(\d+\))?/'],
			"timestamp"	=> 	["name" => "timestamp",		"precision" => 0, 	"regex" => '/timestamp(\(\d+\))?/'],
			"uint"		=> 	["name" => "int unsigned", 	"limit" => 10, 		"regex" => '/int(\(\d+\)) unsigned/'],
		];

		function compileForeignKey(ForeignKeyRelation $relation) {
			return "ALTER TABLE `".$relation->referencingTableName."` ADD CONSTRAINT `".$relation->keyName."` FOREIGN KEY (`".$relation->referencingColumnName."`) REFERENCES `".$relation->sourceTableName."`(`".$relation->sourceColumnName."`) ON DELETE ".static::ForeignKeyModifiers[$relation->onDelete]." ON UPDATE ".static::ForeignKeyModifiers[$relation->onUpdate].";";
		}

		function getNativeBinary($limit) {

			if (empty($limit)) {
				return "blob";
			}

			if ($limit <= 0xff) {
				return "tinyblob";
			}
			else if ($limit <= 0xffff) {
				return "blob";
			}
			else if ($limit <= 0xffffff) {
				return "mediumblob";
			}
			else if ($limit <= 0xffffffff) {
				return "longblob";
			}
			else {
				throw new SqlTypeLengthException("blob", $limit);
			}

		}

		function getNativeInteger($limit = null) {

			$int = "int";
			if (empty($limit)) {
				$limit = 11;
			}
			else if ($limit == 1) {
				$int = "tinyint";
			}
			else if ($limit < 3) {
				$int = "smallint";
			}
			else if ($limit < 4) {
				$int = "mediumint";
			}
			else if ($limit < 5) {
				$int = "int";
			}
			else if ($limit < 8) {
				$int = "bigint";
			}

			return $int."($limit)";

		}

		function getNativeText($limit) {

			$text = "text";
			if (empty($limit)) {}
			else if ($limit <= 0xff) {
				$text = "tinytext";
			}
			else if ($limit <= 0xffff) {
				$text = "text";
			}
			else if ($limit <= 0xffffff) {
				$text = "mediumtext";
			}
			else if ($limit <= 0xffffffff) {
				$text = "longtext";
			}
			else {
				throw new SqlTypeLengthException("text", $limit);
			}

			return $text.(empty($limit) ? "" : "($limit)");

		}

		function getNativeType($type, $limit = null, $precision = null, $scale = null) {

			if ($type == "integer") {
				return $this->getNativeInteger($limit);
			}
			else if ($type == "uint") {
				return $this->getNativeInteger($limit ?? 10)." unsigned";
			}
			else if ($type == "text") {
				return $this->getNativeText($limit);
			}
			else if ($type == "binary") {
				return $this->getNativeBinary($limit);
			}
			else {
				return parent::getNativeType($type, $limit, $precision, $scale);
			}

		}

	}

?>