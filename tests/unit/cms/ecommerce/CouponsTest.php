<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class CouponsTest extends TestCase
{
	/**
	 * 
	 */
	public function testExistsConfig(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$coupons->Config = $config;

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(15);

		$this->assertTrue($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testNoExistsConfig(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
			
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config = $config;

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertFalse($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testExistsDB(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');
		

		$user->id = 1;
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config = $config;
		

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->andReturn($sql);
		$sql->shouldReceive('ROW')->andReturn($this->couponDbRow());

		$this->assertTrue($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testNotExistsDB(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');

		$user->id = 1;
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config = $config;
		

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->andReturn($sql);
		$sql->shouldReceive('ROW')->andReturn([]);

		$this->assertFalse($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testUsedPublicCouponLoggedIn(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');

		$user->id            = 1;
		$user->email         = 'foo@bar.com';
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config     = $config;
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(2)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(2)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(2)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		$sql->shouldReceive('ROW')->times(1)->andReturn($this->couponDbRow());
		
		$this->assertTrue($coupons->used('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testUnUsedPublicCouponLoggedIn(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');

		$user->id            = 1;
		$user->email         = 'foo@bar.com';
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config     = $config;
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(2)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(2)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(2)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		
		$this->assertFalse($coupons->used('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testUsedFromEmailLoggedIn(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');

		$user->id            = 1;
		$user->email         = 'foo@bar.com';
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config     = $config;
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(2)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(2)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(2)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		$sql->shouldReceive('ROW')->times(1)->andReturn($this->couponDbRow());
		
		$this->assertTrue($coupons->used('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testUsedFromEmail(): void
	{
		$coupons    = Mockery::mock('\kanso\cms\ecommerce\Coupons')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$config     = Mockery::mock('\kanso\framework\config\Config');
		$gatekeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$user       = Mockery::mock('\kanso\cms\wrappers\User');

		$user->id            = 1;
		$user->email         = 'foo@bar.com';
		$coupons->Gatekeeper = $gatekeeper;
		$coupons->Config     = $config;
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$coupons->shouldAllowMockingProtectedMethods();
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		
		$this->assertTrue($coupons->used('FOOBAR'));
	}

	/**
	 * 
	 */
	private function couponDbRow(): array
	{
		return
		[
			'id'          => 1,
			'user_id'     => 1,
			'name'        => 'FOOBAR',
			'description' => '15 Percent off',
			'discount'    => 15,
			'date'        => time(),
			'used'        => -1
		];
	}
}

