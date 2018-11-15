<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\password\encrypters;

/**
 * Native PHP hashing with polyfill fallback.
 *
 * @author Joe J. Howard
 */
class NativePHP extends Encrypter implements EncrypterInterface
{
	/**
	 * PHP password hashing constant.
	 *
	 * @see http://php.net/manual/en/password.constants.php
	 * @var int
	 */
	private $algo;

	/**
	 * Constructor.
	 *
	 * @param int $algo PHP password hashing constant
	 * @see   http://php.net/manual/en/password.constants.php
	 */
	public function __construct(int $algo = PASSWORD_DEFAULT)
	{
		$this->algo = $algo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hash(string $string): string
	{
		return password_hash($string, $this->algo, ['cost' => 8]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify(string $string, string $hashed): bool
	{
		return password_verify($string, $hashed);
	}
}
