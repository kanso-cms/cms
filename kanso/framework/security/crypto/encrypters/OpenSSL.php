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
	 * @var string
	 */
	protected $ivSize;

	/**
	 * Initialization vector size.
	 *
	 * @var string
	 */
	protected $ciphers;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string $key    Encryption key
	 * @param string $cipher Cipher
	 */
	public function __construct(string $key, string $cipher = null)
	{
		$this->loadCyphers();

		$this->key = $key;

		$this->cipher = !$cipher || !in_array($cipher, $this->ciphers) ? 'AES-256-CTR' : $cipher;

		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	/**
	 * Load compatible ciphers.
	 *
	 * @access private
	 */
	private function loadCyphers()
	{
		$this->ciphers = array_filter(openssl_get_cipher_methods(), function($cypher)
		{
			if (strpos(strtolower($cypher), 'gcm') !== false || strpos(strtolower($cypher), 'ccm') !== false || strpos(strtolower($cypher), 'des-') !== false)
			{
			    return false;
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
