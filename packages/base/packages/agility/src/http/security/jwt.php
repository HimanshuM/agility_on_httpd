<?php

namespace Agility\Http\Security;

use Exception;

	class Jwt {

		const HS256 = "HS256";

		const Algorithms = [
			"HS256" => "sha256"
		];

		static function getComponents() {

			$components = explode(".", $token);
			if (count($components) != 3) {
				return false;
			}

			return $components;

		}

		static function decode($token, $secret) {

			if (($components = Jwt::getComponents($token))) {
				throw new Exception("Invalid JWT token");
			}

			list($headerEncoded, $dataEncoded, $signature) = $components;

			$header = json_decode(base64_decode($headerEncoded));
			$data = json_decode(base64_decode($dataEncoded));

			$signatureFound = hash_hmac(Jwt::Algorithms[$header["alg"]], $headerEncoded.".".$dataEncoded, $secret);
			if ($signatureFound == $signature) {
				return $data;
			}

			return false;

		}

		static function encode($data, $encryption, $secret) {

			if (!isset($encryption, Jwt::Algorithms)) {
				throw new Exception("Invalid JWT encryption algorithm");
			}

			$header = [
				"alg" => $encryption
				"typ" => "jwt",
			];

			$headerEncoded = base64_encode(json_encode($header));
			$dataEncoded = base64_encode(json_encode($dataEncoded));

			$signature = hash_hmac(Jwt::Algorithms[$encryption, $headerEncoded.".".$dataEncoded, $secret);

			return $headerEncoded.".".$dataEncoded.".".$signature;

		}

	}

?>