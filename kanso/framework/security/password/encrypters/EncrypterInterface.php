<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\password\encrypters;

/**
 * Password hashing interface.
 *
 * @author Joe J. Howard
 */
interface EncrypterInterface
{
	/**
	 * Hashes a password.
	 *
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function hash(string $string): string;

	/**
	 * Verifies a hashed password with an unhashed one.
	 *
	 * @param  string $string String to decrypt
	 * @return bool
	 */
	public function verify(string $string, string $hashed): bool;
}
