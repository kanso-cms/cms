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
class CartTest extends TestCase
{
	/**
	 *
	 */
	public function testNotEmptySession()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$session    = $this->fakeSession();

		$cart->Session    = $session;
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertFalse($cart->isEmpty());
	}

	/**
	 *
	 */
	public function testEmptySession()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$session    = $this->fakeSessionEmpty();

		$cart->Session    = $session;
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertTrue($cart->isEmpty());
	}

	/**
	 *
	 */
	public function testNotEmptyDB()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$user       = $this->fakeUser();

		$cart->shouldAllowMockingProtectedMethods();
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(true);

		$gateKeeper->shouldReceive('getUser')->andReturn($user);

		$cart->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('shopping_cart_items')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('FIND_ALL')->andReturn($this->shoppingCartEntries());

		$this->assertFalse($cart->isEmpty());
	}

	/**
	 *
	 */
	public function testEmptyDb()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$user       = $this->fakeUser();

		$cart->shouldAllowMockingProtectedMethods();
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(true);

		$gateKeeper->shouldReceive('getUser')->andReturn($user);

		$cart->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('shopping_cart_items')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('FIND_ALL')->andReturn([]);

		$this->assertTrue($cart->isEmpty());
	}

	/**
	 *
	 */
	public function testCountSession()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$session    = $this->fakeSession();

		$cart->Session    = $session;
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals(3, $cart->count());
	}

	/**
	 *
	 */
	public function testCountSessionEmpty()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$session    = $this->fakeSessionEmpty();

		$cart->Session    = $session;
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals(0, $cart->count());
	}

	/**
	 *
	 */
	public function testCountDb()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$user       = $this->fakeUser();

		$cart->shouldAllowMockingProtectedMethods();
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(true);

		$gateKeeper->shouldReceive('getUser')->andReturn($user);

		$cart->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('shopping_cart_items')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('FIND_ALL')->andReturn($this->shoppingCartEntries());

		$this->assertEquals(3, $cart->count());
	}

	/**
	 *
	 */
	public function testCountDbEmpty()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$user       = $this->fakeUser();

		$cart->shouldAllowMockingProtectedMethods();
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(true);

		$gateKeeper->shouldReceive('getUser')->andReturn($user);

		$cart->shouldReceive('sql')->andReturn($sql);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('shopping_cart_items')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('FIND_ALL')->andReturn([]);

		$this->assertEquals(0, $cart->count());
	}

	/**
	 *
	 */
	public function testClearSession()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$session    = Mockery::mock('\kanso\framework\http\session\Session');

		$cart->Session    = $session;
		$cart->Gatekeeper = $gateKeeper;

		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$session->shouldReceive('remove')->with('shopping_cart_items');

		$cart->clear();
	}

	/**
	 *
	 */
	public function testClearDB()
	{
		$cart       = Mockery::mock('\kanso\cms\ecommerce\Cart')->makePartial();
		$gateKeeper = Mockery::mock('\kanso\cms\auth\Gatekeeper');
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$session    = Mockery::mock('\kanso\framework\http\session\Session');
		$user       = $this->fakeUser();

		$cart->shouldAllowMockingProtectedMethods();
		$cart->Session = $session;
		$cart->Gatekeeper = $gateKeeper;
		$cart->shouldReceive('sql')->andReturn($sql);
		$gateKeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gateKeeper->shouldReceive('getUser')->andReturn($user);
		$session->shouldReceive('remove')->with('shopping_cart_items');
		$sql->shouldReceive('DELETE_FROM')->with('shopping_cart_items')->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(3);

		$cart->clear();
	}

	/**
	 *
	 */
	private function fakeSession()
	{
		return new fakeSessionWithProducts;
	}

	/**
	 *
	 */
	private function fakeSessionEmpty()
	{
		return new fakeSessionWithoutProducts;
	}

	/**
	 *
	 */
	private function fakeUser()
	{
		return new fakeUser;
	}

	/**
	 *
	 */
	private function shoppingCartEntries()
	{
		return
		[
			[
				'id'         => 1,
				'product_id' => 1,
				'offer_id'   => 'offer-id-1',
				'quantity'   => 1,
			],
			[
				'id'         => 2,
				'product_id' => 2,
				'offer_id'   => 'offer-id-2',
				'quantity'   => 2,
			],
		];
	}

}

class fakeUser
{
	public $id = 1;
}

class fakeSessionWithoutProducts
{
	public function get(string $key)
	{
		return false;
	}
}

class fakeSessionWithProducts
{
	public function get(string $key)
	{
		if ($key === 'shopping_cart_items')
		{
			return
			[
				[
					'id'         => 1,
					'product_id' => 1,
					'offer_id'   => 'offer-id-1',
					'quantity'   => 1,
				],
				[
					'id'         => 2,
					'product_id' => 2,
					'offer_id'   => 'offer-id-2',
					'quantity'   => 2,
				],
			];
		}
	}
}
