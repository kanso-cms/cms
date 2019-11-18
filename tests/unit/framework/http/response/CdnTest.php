<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\CDN;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class CdnTest extends TestCase
{
	/**
	 *
	 */
	public function testFilter(): void
	{
		$cdn = new CDN('https://foo.com', 'https://cdn.foo.com');

		$cdn->enable();

		// Test image
		$this->assertEquals('<img title="foo" alt="bar" src="https://cdn.foo.com/bar.png">', $cdn->filter('<img title="foo" alt="bar" src="https://foo.com/bar.png">'));
		$this->assertEquals('<img src="https://cdn.foo.com/bar.png" title="foo" alt="bar">', $cdn->filter('<img src="https://foo.com/bar.png" title="foo" alt="bar">'));

		// Test script
		$this->assertEquals('<script type="text/javascript" src="https://cdn.foo.com/script.js"></script>', $cdn->filter('<script type="text/javascript" src="https://foo.com/script.js"></script>'));
		$this->assertEquals('<script src="https://cdn.foo.com/script.js" type="text/javascript"></script>', $cdn->filter('<script src="https://foo.com/script.js" type="text/javascript"></script>'));

		// Test CSS
		$this->assertEquals('<link rel="stylesheet" href="https://cdn.foo.com/styles.css">', $cdn->filter('<link rel="stylesheet" href="https://foo.com/styles.css">'));
		$this->assertEquals('<link href="https://cdn.foo.com/styles.css" rel="stylesheet">', $cdn->filter('<link href="https://foo.com/styles.css" rel="stylesheet">'));

		// Test favicons
		$this->assertEquals('<link rel="apple-touch-icon" href="https://cdn.foo.com/bar.png">', $cdn->filter('<link rel="apple-touch-icon" href="https://foo.com/bar.png">'));
		$this->assertEquals('<link href="https://cdn.foo.com/bar.png" rel="apple-touch-icon">', $cdn->filter('<link href="https://foo.com/bar.png" rel="apple-touch-icon">'));

		// Test background urls
		$this->assertEquals('<div style="background:url(https://cdn.foo.com/bar.png)"></div>', $cdn->filter('<div style="background:url(https://foo.com/bar.png)"></div>'));
		$this->assertEquals('<div style="background-image:url(https://cdn.foo.com/bar.png)"></div>', $cdn->filter('<div style="background-image:url(https://foo.com/bar.png)"></div>'));
		$this->assertEquals('<div style="background-image:url(\'https://cdn.foo.com/bar.png\')"></div>', $cdn->filter('<div style="background-image:url(\'https://foo.com/bar.png\')"></div>'));
	}

	/**
	 *
	 */
	public function testDisabled(): void
	{
		$cdn = new CDN('https://foo.com', 'https://cdn.foo.com');

		$cdn->disable();

		// Test image
		$this->assertEquals('<img title="foo" alt="bar" src="https://foo.com/bar.png">', $cdn->filter('<img title="foo" alt="bar" src="https://foo.com/bar.png">'));
		$this->assertEquals('<img src="https://foo.com/bar.png" title="foo" alt="bar">', $cdn->filter('<img src="https://foo.com/bar.png" title="foo" alt="bar">'));

		// Test script
		$this->assertEquals('<script type="text/javascript" src="https://foo.com/script.js"></script>', $cdn->filter('<script type="text/javascript" src="https://foo.com/script.js"></script>'));
		$this->assertEquals('<script src="https://foo.com/script.js" type="text/javascript"></script>', $cdn->filter('<script src="https://foo.com/script.js" type="text/javascript"></script>'));

		// Test CSS
		$this->assertEquals('<link rel="stylesheet" href="https://foo.com/styles.css">', $cdn->filter('<link rel="stylesheet" href="https://foo.com/styles.css">'));
		$this->assertEquals('<link href="https://foo.com/styles.css" rel="stylesheet">', $cdn->filter('<link href="https://foo.com/styles.css" rel="stylesheet">'));

		// Test favicons
		$this->assertEquals('<link rel="apple-touch-icon" href="https://foo.com/bar.png">', $cdn->filter('<link rel="apple-touch-icon" href="https://foo.com/bar.png">'));
		$this->assertEquals('<link href="https://foo.com/bar.png" rel="apple-touch-icon">', $cdn->filter('<link href="https://foo.com/bar.png" rel="apple-touch-icon">'));

		// Test background urls
		$this->assertEquals('<div style="background:url(https://foo.com/bar.png)"></div>', $cdn->filter('<div style="background:url(https://foo.com/bar.png)"></div>'));
		$this->assertEquals('<div style="background-image:url(https://foo.com/bar.png)"></div>', $cdn->filter('<div style="background-image:url(https://foo.com/bar.png)"></div>'));
		$this->assertEquals('<div style="background-image:url(\'https://foo.com/bar.png\')"></div>', $cdn->filter('<div style="background-image:url(\'https://foo.com/bar.png\')"></div>'));

	}
}
