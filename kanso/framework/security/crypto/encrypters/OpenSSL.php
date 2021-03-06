<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\crypto\encrypters;

/**
 * Encryption/Decryption interface.
 *
 * @author Joe J. Howard
 */
class OpenSSL extends Encrypter implements EncrypterInterface
{
	/**
	 * Key used to encrypt/decrypt string.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The cipher method to use for encryption.
	 *
	 * @var string
	 */
	protected $cipher;

	/**
	 * Initialization vector size.
	 *
	 * @var int
	 */
	protected $ivSize;

	/**
	 * Initialization vector size.
	 *
	 * @var array
	 */
	protected $ciphers;

	/**
	 * Cyphers we don't use.
	 *
	 * @var array
	 */
	protected $nonCyphers =
	[
		'aes-',
		'bf-',
		'camellia-',
		'cast5-',
		'ccm-',
		'des-',
		'gcm-',
		'id-',
	];

	/**
	 * Constructor.
	 *
	 * @param string $key    Encryption key
	 * @param string $cipher Cipher (optional) (default 'AES-256-ECB')
	 */
	public function __construct(string $key, string $cipher = 'AES-256-ECB')
	{
		$this->loadCyphers();

		$this->key = $key;

		$this->cipher = !in_array($cipher, $this->ciphers) ? 'AES-256-ECB' : $cipher;

		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	/**
	 * Load compatible ciphers.
	 */
	private function loadCyphers(): void
	{
		$this->ciphers = array_filter(openssl_get_cipher_methods(), function($cypher)
		{
			foreach ($this->nonCyphers as $nonCypher)
			{
				if (strpos(strtolower($cypher), $nonCypher) !== false)
				{
			    	return false;
				}
			}

			return true;
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function encrypt(string $string): string
	{
		$iv = openssl_random_pseudo_bytes($this->ivSize);

		$key = $this->deriveKey($this->key, $iv, 32);

		return base64_encode($iv . openssl_encrypt($string, $this->cipher, $key, 0, $iv));
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrypt(string $string)
	{
		$string = base64_decode($string, true);

		if($string === false)
		{
			return false;
		}

		$iv = mb_substr($string, 0, $this->ivSize, '8bit');

		$string = mb_substr($string, $this->ivSize, null, '8bit');

		$key = $this->deriveKey($this->key, $iv, 32);

		return openssl_decrypt($string, $this->cipher, $key, 0, $iv);
	}
}
