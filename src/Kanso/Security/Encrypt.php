<?php

namespace Kanso\Security;

/**
 * Encrypt/Hash data one-way with salt
 *
 * This class provides and abstraction layer for hashing/verifying passwords
 * hrough PHP 5 >= 5.5.0 native password_hash function. If the function
 * doesn't exist, a pollyfill fallback is used
 *
 */
class Encrypt {
	
	/**
	 * Hash data - one-way
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function hash($string)
	{
		if (!function_exists('password_hash')) require_once __DIR__.DIRECTORY_SEPARATOR.'password.php';
		return password_hash($string, PASSWORD_BCRYPT);
	}

	/**
	 * Verify a string against a hash 
	 *
	 * @param  string $string
	 * @return boolean
	 */
	public static function verify($string, $hashed)
	{
		if (!function_exists('password_verify')) require_once __DIR__.DIRECTORY_SEPARATOR.'password.php';
		return password_verify($string, $hashed);
	}

}