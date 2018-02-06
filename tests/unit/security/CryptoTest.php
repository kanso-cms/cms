<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\security\crypto\encrypters;

use Mockery;
use tests\TestCase;
use kanso\framework\security\Crypto;

/**
 * @group unit
 */
class CryptoTest extends TestCase
{
	/**
	 *
	 */
	public function testEncrypt()
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		$signer = Mockery::mock('\kanso\framework\security\crypto\Signer');

		$encrypter = Mockery::mock('\kanso\framework\security\crypto\encrypters\OpenSSL');

		$password = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$crypto = new Crypto($signer, $encrypter, $password);

		$encrypter->shouldReceive('encrypt')->withArgs([$data])->andReturn('ENCRYPTED');

		$signer->shouldReceive('sign')->withArgs(['ENCRYPTED'])->andReturn('SIGNED AND ENCRYPTED');

		$crypto->encrypt($data);
	}

	/**
	 *
	 */
	public function testDecrypt()
	{
		$signer = Mockery::mock('\kanso\framework\security\crypto\Signer');

		$encrypter = Mockery::mock('\kanso\framework\security\crypto\encrypters\OpenSSL');

		$password = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$crypto = new Crypto($signer, $encrypter, $password);

		$signer->shouldReceive('validate')->withArgs(['SIGNED AND ENCRYPTED'])->andReturn('UNSIGNED ENCRYPTEDSTRING');

		$encrypter->shouldReceive('decrypt')->withArgs(['UNSIGNED ENCRYPTEDSTRING'])->andReturn('raw data');

		$crypto->decrypt('SIGNED AND ENCRYPTED');
	}
}