<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\security\spam;

use kanso\framework\security\spam\SpamProtector;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class SpamProtectorTest extends TestCase
{
	/**
	 *
	 */
	public function testIpUnWhitelist(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.whitelist.ipaddresses'])->andReturn(['134.233.2443.1', '134.233.2443.2']);
		$config->shouldReceive('set')->withArgs(['spam.whitelist.ipaddresses', ['134.233.2443.2']]);
		$config->shouldReceive('save');

		$spam->unWhitelistIpAddress('134.233.2443.1');
	}

	/**
	 *
	 */
	public function testIpWhitelist(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.whitelist.ipaddresses'])->andReturn(['134.233.2443.2']);
		$config->shouldReceive('set')->withArgs(['spam.whitelist.ipaddresses', ['134.233.2443.1', '134.233.2443.2']]);
		$config->shouldReceive('save');

		$spam->whitelistIpAddress('134.233.2443.1');
	}

	/**
	 *
	 */
	public function testIpUnBlacklist(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.ipaddresses'])->andReturn(['134.233.2443.1', '134.233.2443.2']);
		$config->shouldReceive('set')->withArgs(['spam.blacklist.ipaddresses', ['134.233.2443.2']]);
		$config->shouldReceive('save');

		$spam->unBlacklistIpAddress('134.233.2443.1');
	}

	/**
	 *
	 */
	public function testIpBlacklist(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.ipaddresses'])->andReturn(['134.233.2443.2']);
		$config->shouldReceive('set')->withArgs(['spam.blacklist.ipaddresses', ['134.233.2443.1', '134.233.2443.2']]);
		$config->shouldReceive('save');

		$spam->blacklistIpAddress('134.233.2443.1');
	}

	/**
	 *
	 */
	public function testIsIpBlacklisted(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.ipaddresses'])->andReturn(['134.233.2443.2']);

		$this->assertFalse($spam->isIpBlacklisted('134.233.2443.1'));

		$this->assertTrue($spam->isIpBlacklisted('134.233.2443.2'));
	}

	/**
	 *
	 */
	public function testIsIpWhitelisted(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.whitelist.ipaddresses'])->andReturn(['134.233.2443.2']);

		$this->assertFalse($spam->isIpWhiteListed('134.233.2443.1'));

		$this->assertTrue($spam->isIpWhiteListed('134.233.2443.2'));
	}

	/**
	 *
	 */
	public function testIsSpam(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.constructs'])->andReturn(['Wonderful post']);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.urls'])->andReturn(['xxx.com']);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.words'])->andReturn(['fuck']);

		$config->shouldReceive('get')->withArgs(['spam.blacklist.html'])->andReturn(['(javascript:)']);

		$gibberish->shouldReceive('test')->withArgs(['Hello world! thanks for this post'])->andReturn(false);

		$gibberish->shouldReceive('test')->withArgs(['fsf fafdr24r2 kokfsfsdf 423 gfssf'])->andReturn(true);

		$this->assertTrue($spam->isSpam('Wonderful post! You should check my post on something.'));

		$this->assertTrue($spam->isSpam('Hello world! thanks for fuck https://xxx.com'));

		$this->assertTrue($spam->isSpam('Hello world! thanks for fuck'));

		$this->assertTrue($spam->isSpam('<a href="foobar" onlick="(javascript:)"'));

		$this->assertFalse($spam->isSpam('Hello world! thanks for this post'));

		$this->assertTrue($spam->isSpam('fsf fafdr24r2 kokfsfsdf 423 gfssf'));
	}

	/**
	 *
	 */
	public function testRating(): void
	{
		$gibberish = $this->mock('\kanso\framework\security\spam\gibberish\Gibberish');

		$config = $this->mock('\kanso\framework\config\Config');

		$spam = new SpamProtector($gibberish, $config);

		$config->shouldReceive('get')->withArgs(['spam.graylist.constructs'])->andReturn(['wonderful']);

		$config->shouldReceive('get')->withArgs(['spam.graylist.urls'])->andReturn(['xxx.com']);

		$config->shouldReceive('get')->withArgs(['spam.graylist.words'])->andReturn(['fuck']);

		$config->shouldReceive('get')->withArgs(['spam.graylist.html'])->andReturn(['(javascript:)']);

		$this->assertEquals(-1, $spam->rating('fuck fuck'));

		$this->assertEquals(3, $spam->rating('fantastic fuck article xxx.com you (javascript:) should.'));
	}
}
