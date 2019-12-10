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
		$mocks = $this->getCouponMocks();
		extract($mocks);

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(15);

		$this->assertTrue($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testNoExistsConfig(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertFalse($coupons->exists('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testExistsDB(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
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
		$mocks = $this->getCouponMocks();
		extract($mocks);

		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
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
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
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
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
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
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
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
	public function testUnUsedFromEmail(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		
		$this->assertFalse($coupons->used('FOOBAR', 'foo@bar.com'));
	}

	/**
	 * 
	 */
	public function testUsedFromEmail(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(true);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('used_public_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('coupon_name', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('email', '=', 'foo@bar.com')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($this->couponDbRow());
		
		$this->assertTrue($coupons->used('FOOBAR', 'foo@bar.com'));
	}

	/**
	 * 
	 */
	public function testUsedNoExists(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		
		$coupons->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('used', '=', true)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);
		
		$this->assertFalse($coupons->used('FOOBAR'));
	}
	/**
	 * 
	 */
	public function testUnusedNoExists(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$this->assertTrue($coupons->used('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testDiscountExists(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(15);
		$this->assertEquals($coupons->discount('FOOBAR'), 15);
	}

	/**
	 * 
	 */
	public function testDiscountFromDb(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldReceive('sql')->andReturn($sql);
		
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('used', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($this->couponDbRow());

		$this->assertEquals($coupons->discount('FOOBAR'), 15);
	}

	/**
	 * 
	 */
	public function testNoDiscountFromDb(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldReceive('sql')->andReturn($sql);
		
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('used', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertEquals($coupons->discount('FOOBAR'), false);
	}

	/**
	 * 
	 */
	public function testSetUsedPublic(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(15);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldReceive('sql')->andReturn($sql);
		
		$sql->shouldReceive('INSERT_INTO')->with('used_public_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with(['user_id' => 1, 'email' =>'foo@bar.com', 'coupon_name' => 'FOOBAR'])->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn([1]);

		$this->assertTrue($coupons->setUsed('FOOBAR', 'foo@bar.com'));
	}

	/**
	 * 
	 */
	public function testSetUsedPrivate(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('used', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($this->couponDbRow());

		$update = $this->couponDbRow();
		unset($update['id']);
		
		$sql->shouldReceive('UPDATE')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('SET')->with($update)->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn([1]);

		$this->assertTrue($coupons->setUsed('FOOBAR'));
	}

	/**
	 * 
	 */
	public function testSetUsedPrivateNoExists(): void
	{
		$mocks = $this->getCouponMocks();
		extract($mocks);
		
		$config->shouldReceive('get')->with('ecommerce.coupons.FOOBAR')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$coupons->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('code', '=', 'FOOBAR')->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('used', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertFalse($coupons->setUsed('FOOBAR'));
	}


	/**
	 * 
	 */
	private function getCouponMocks(): array
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
		$coupons->shouldAllowMockingProtectedMethods();

		return
		[
			'coupons'    => $coupons,
			'sql'        => $sql,
			'config'     => $config,
			'gatekeeper' => $gatekeeper,
			'user'       => $user,
		];
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

