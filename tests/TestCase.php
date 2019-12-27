<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case.
 *
 * @author Frederic G. Østby
 */
abstract class TestCase extends PHPUnitTestCase
{
	use MockeryPHPUnitIntegration;

	protected function mock(string $class, array $args = [])
	{
		if (!empty($args))
		{
			return Mockery::mock($class, $args);
		}

		return Mockery::mock($class);
	}
}
