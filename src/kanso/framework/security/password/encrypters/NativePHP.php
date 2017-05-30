<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\password\encrypters;

/**
 * Native PHP hashing with polyfill fallback
 *
 * @author Joe J. Howard
 */
class NativePHP extends Encrypter implements EncrypterInterface
{
	/**
	 * PHP password hashing constant
	 *
	 * @see http://php.net/manual/en/password.constants.php
	 * @var int
	 */
	private $algo;

	/**
	 * Constructor
	 *
	 * @param int $algo PHP password hashing constant
	 * @see   http://php.net/manual/en/password.constants.php
	 */
	public function __construct(int $algo)
	{
		$this->algo = $algo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hash(string $string): string
	{
		if (!function_exists('password_hash'))
		{
			require_once __DIR__.DIRECTORY_SEPARATOR.'_polyfill.php';
		}
		
		return password_hash($string, $this->algo, ['cost' => 8]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify(string $string, string $hashed): bool
	{
		if (!function_exists('password_verify'))
		{
			require_once __DIR__.DIRECTORY_SEPARATOR.'_polyfill.php';
		}
		
		return password_verify($string, $hashed);
	}
}
