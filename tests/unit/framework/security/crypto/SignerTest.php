<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\security\crypto;

use kanso\framework\security\crypto\Signer;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
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
