<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Security\Password\Encrypters;

/**
 * Password hashing interface
 *
 * @author Joe J. Howard
 */
interface EncrypterInterface
{
	/**
	 * Hashes a password.
	 *
	 * @access public
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function hash(string $string): string;

	/**
	 * Verifies a hashed password with an unhashed one.
	 *
	 * @access public
	 * @param  string      $string String to decrypt
	 * @return string|bool
	 */
	public function verify(string $string, string $hashed): bool;
}
