<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\Status;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class StatusTest extends TestCase
{
	/**
	 *
	 */
	public function testSet()
	{
		$status = new Status;

		$this->assertEquals(200, $status->get());

		$status->set(404);

		$this->assertEquals(404, $status->get());
	}

	/**
	 *
	 */
	public function testMessage()
	{
		$status = new Status;

		$this->assertEquals('OK', $status->message());

		$status->set(404);

		$this->assertEquals('Not Found', $status->message());
	}

	/**
	 *
	 */
	public function testHelpers()
	{
		$status = new Status;

		$this->assertTrue($status->isOk());

		$status->set(102);

		$this->assertTrue($status->isInformational());

		$status->set(204);

		$this->assertTrue($status->isEmpty());

		$status->set(206);

		$this->assertTrue($status->isSuccessful());

		$status->set(302);

		$this->assertTrue($status->isRedirect());

		$status->set(403);

		$this->assertTrue($status->isForbidden());

		$status->set(404);

		$this->assertTrue($status->isNotFound());

		$status->set(440);

		$this->assertTrue($status->isClientError());

		$status->set(500);

		$this->assertTrue($status->isServerError());
	}
}
