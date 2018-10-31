<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\onion;

use kanso\framework\crawler\CrawlerDetect;
use kanso\framework\crawler\fixtures\Exclusions;
use kanso\framework\crawler\fixtures\Inclusions;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class CrawlerTest extends TestCase
{
	/**
	 *
	 */
	public function testNonBot()
	{
		$headers = Mockery::mock('\kanso\framework\http\request\Headers');

		$headers->HTTP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

		$userAgent = new CrawlerDetect($headers, new Inclusions, new Exclusions);

		$this->assertFalse($userAgent->isCrawler());
	}

	/**
	 *
	 */
	public function testBot()
	{
		$headers = Mockery::mock('\kanso\framework\http\request\Headers');

		$headers->HTTP_USER_AGENT = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';

		$userAgent = new CrawlerDetect($headers, new Inclusions, new Exclusions);

		$this->assertTrue($userAgent->isCrawler());
	}
}
