<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\security\crypto;

use kanso\framework\security\Crypto;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class CryptoTest extends TestCase
{
	/**
	 *
	 */
	public function testEncrypt(): void
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		$signer = $this->mock('\kanso\framework\security\crypto\Signer');

		$encrypter = $this->mock('\kanso\framework\security\crypto\encrypters\OpenSSL');

		$password = $this->mock('\kanso\framework\security\password\encrypters\NativePHP');

		$crypto = new Crypto($signer, $encrypter, $password);

		$encrypter->shouldReceive('encrypt')->withArgs([$data])->andReturn('ENCRYPTED');

		$signer->shouldReceive('sign')->withArgs(['ENCRYPTED'])->andReturn('SIGNED AND ENCRYPTED');

		$crypto->encrypt($data);
	}

	/**
	 *
	 */
	public function testDecrypt(): void
	{
		$signer = $this->mock('\kanso\framework\security\crypto\Signer');

		$encrypter = $this->mock('\kanso\framework\security\crypto\encrypters\OpenSSL');

		$password = $this->mock('\kanso\framework\security\password\encrypters\NativePHP');

		$crypto = new Crypto($signer, $encrypter, $password);

		$signer->shouldReceive('validate')->withArgs(['SIGNED AND ENCRYPTED'])->andReturn('UNSIGNED ENCRYPTEDSTRING');

		$encrypter->shouldReceive('decrypt')->withArgs(['UNSIGNED ENCRYPTEDSTRING'])->andReturn('raw data');

		$crypto->decrypt('SIGNED AND ENCRYPTED');
	}
}
