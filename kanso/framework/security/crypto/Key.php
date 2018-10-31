<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\crypto;

/**
 * Encryption key converter.
 *
 * @author Joe J. Howard
 */
class Key
{
	/**
	 * Converts a binary key into its hexadecimal representation.
	 *
	 * @access public
	 * @param  string $key Binary key
	 * @return string
	 */
	public static function encode(string $key): string
	{
		return 'hex:' . bin2hex($key);
	}

	/**
	 * Converts a hexadecimal key into its binary representation.
	 *
	 * @access public
	 * @param  string $key Encoded key
	 * @return string
	 */
	public static function decode(string $key): string
	{
		if(strpos($key, 'hex:') === 0)
		{
			return hex2bin(mb_substr($key, 4, null, '8bit'));
		}

		return $key;
	}

	/**
	 * Generates a key.
	 *
	 * @access public
	 * @param  int    $length Key length
	 * @return string
	 */
	public static function generate(int $length = 32): string
	{
		return random_bytes($length);
	}

	/**
	 * Generates a hex encoded key.
	 *
	 * @access public
	 * @param  int    $length Key length
	 * @return string
	 */
	public static function generateEncoded(int $length = 32): string
	{
		return static::encode(static::generate($length));
	}
}
