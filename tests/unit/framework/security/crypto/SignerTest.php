<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\security\crypto;

use kanso\framework\security\crypto\Signer;
use tests\TestCase;

/**
 * @group unit
 */
class SignerTest extends TestCase
{
	/**
	 *
	 */
	public function testSigner()
	{
		$data = 'foobar!!$#$@::32342:#"$#@!$P:{';

		$signer = new Signer('secret-code');

		$signed = $signer->sign($data);

		$unsigned = $signer->validate($signed);

		$this->assertEquals($data, $unsigned);
	}
}
