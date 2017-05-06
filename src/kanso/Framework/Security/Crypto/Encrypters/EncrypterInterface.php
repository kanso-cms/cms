<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Security\Crypto\Encrypters;

/**
 * Encryption/Decryption interface
 *
 * @author Joe J. Howard
 */
interface EncrypterInterface
{
	/**
	 * Encrypts string.
	 *
	 * @access public
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function encrypt(string $string): string;

	/**
	 * Decrypts string.
	 *
	 * @access public
	 * @param  string      $string String to decrypt
	 * @return string|bool
	 */
	public function decrypt(string $string);
}
