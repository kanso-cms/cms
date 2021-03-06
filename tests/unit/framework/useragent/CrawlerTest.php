<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\useragent;

use kanso\framework\crawler\CrawlerDetect;
use kanso\framework\crawler\fixtures\Inclusions;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class CrawlerTest extends TestCase
{
	/**
	 *
	 */
	public function testNonBot(): void
	{
		$headers = $this->mock('\kanso\framework\http\request\Headers');

		$headers->HTTP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

		$userAgent = new CrawlerDetect($headers, new Inclusions);

		$this->assertFalse($userAgent->isCrawler());
	}

	/**
	 *
	 */
	public function testBot(): void
	{
		$headers = $this->mock('\kanso\framework\http\request\Headers');

		$headers->HTTP_USER_AGENT = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';

		$userAgent = new CrawlerDetect($headers, new Inclusions);

		$this->assertTrue($userAgent->isCrawler());
	}
}
