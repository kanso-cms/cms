<?php

namespace Kanso\Auth\Cookie;

class Signer
{
	/**
	 * MAC length.
	 *
	 * @var int
	 */

	const MAC_LENGTH = 64;

	/**
	 * Secret used to sign and validate strings.
	 *
	 * @var string
	 */

	protected $secret;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $secret  Secret used to sign and validate strings
	 */

	public function __construct($secret)
	{
		$this->secret = $secret;
	}

	/**
	 * Returns the signature.
	 *
	 * @access  protected
	 * @param   string     $string  The string you want to sign
	 * @return  string
	 */

	protected function getSignature($string)
	{
		return hash_hmac('sha256', $string, $this->secret);
	}

	/**
	 * Returns a signed string.
	 *
	 * @access  public
	 * @param   string  $string  The string you want to sign
	 * @return  string
	 */

	public function sign($string)
	{
		return $this->getSignature($string) . $string;
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 *
	 * @access  public
	 * @param   string          $string  The string you want to validate
	 * @return  string|boolean
	 */

	public function validate($string)
	{
		$validated = substr($string, static::MAC_LENGTH);

		if(self::compare($this->getSignature($validated), substr($string, 0, static::MAC_LENGTH)))
		{
			return $validated;
		}

		return false;
	}

	private static function compare($string1, $string2)
	{
		$string1 = (string) $string1;
		$string2 = (string) $string2;

		$string1Length = strlen($string1);
		$string2Length = strlen($string2);

		$minLength = min($string1Length, $string2Length);

		$result = $string1Length ^ $string2Length;

		for($i = 0; $i < $minLength; $i++)
		{
			$result |= ord($string1[$i]) ^ ord($string2[$i]);
		}

		return $result === 0;
	}
}