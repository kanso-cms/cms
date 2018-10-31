<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\security\crypto\encrypters;

use kanso\framework\security\crypto\encrypters\OpenSSL;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class OpenSSLTest extends TestCase
{
	/**
	 *
	 */
	public function testEncryptDecrypt()
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		$encrypter = new OpenSSL('secret-code', 'AES-256-ECB');

		$hashed = $encrypter->encrypt($data);

		$this->assertEquals($data, $encrypter->decrypt($hashed));
	}

	/**
	 *
	 */
	public function testCyphers()
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		foreach (openssl_get_cipher_methods() as $cypher)
		{
			$encrypter = new OpenSSL('secret-code', $cypher);

			$hashed = $encrypter->encrypt($data);

			$this->assertEquals($data, $encrypter->decrypt($hashed));
		}
	}
}
