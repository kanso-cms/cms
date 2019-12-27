<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\analytics;

use kanso\cms\analytics\Analytics;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class AnalyticsTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructor(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$this->assertTrue($analytics instanceof Analytics);
	}

	/**
	 *
	 */
	public function testGoogleTrackingCode(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$this->assertTrue($analytics->googleTrackingCode() !== '');
	}

	/**
	 *
	 */
	public function testGoogleTrackingCodeLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertTrue($analytics->googleTrackingCode() !== '');
	}

	/**
	 *
	 */
	public function testFacebookTrackingCode(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$this->assertTrue($analytics->facebookTrackingCode() !== '');
	}

	/**
	 *
	 */
	public function testFacebookTrackingCodeLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$this->assertTrue($analytics->facebookTrackingCode() !== '');
	}

	/**
	 *
	 */
	public function testFacebookTrackingProductView(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$ecommerce->shouldReceive('products')->andReturn($products);
		$products->shouldReceive('offers')->with(1)->andReturn([['name' => 'product offer', 'sale_price' => 19.95]]);
		$query->shouldReceive('the_post_id')->andReturn(1);
		$query->shouldReceive('the_title')->andReturn('Product Title');
		$query->shouldReceive('the_categories_list')->andReturn('Cat 1 > Cat 2');
		$query->shouldReceive('rewind_posts');
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');

		$this->assertTrue($analytics->facebookTrackingProductView() !== '');
	}

	/**
	 *
	 */
	public function testGoogleTrackingProductView(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$ecommerce->shouldReceive('products')->andReturn($products);
		$products->shouldReceive('offers')->with(1)->andReturn([['name' => 'product offer', 'sale_price' => 19.95]]);
		$query->shouldReceive('the_post_id')->andReturn(1);
		$query->shouldReceive('the_title')->andReturn('Product Title');
		$query->shouldReceive('the_categories_list')->andReturn('Cat 1 > Cat 2');
		$query->shouldReceive('rewind_posts');
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');

		$this->assertTrue($analytics->googleTrackingProductView() !== '');
	}

	/**
	 *
	 */
	public function testGoogleTrackingStartCheckout(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$items =
		[
			[
				'product'  => 1,
                'offer'    => ['name' => 'Foobar', 'sale_price' => 19.95],
                'quantity' => 1,
			],
			[
				'product'  => 2,
                'offer'    => ['name' => 'Foobar', 'sale_price' => 19.95],
                'quantity' => 2,
			],
		];

		$ecommerce->shouldReceive('cart')->andReturn($cart);
		$cart->shouldReceive('items')->andReturn($items);
		$cart->shouldReceive('subTotal')->andReturn(29.95);
		$cart->shouldReceive('shippingCost')->andReturn(9.95);

		$query->shouldReceive('the_title')->andReturn('Product Title');
		$query->shouldReceive('the_categories_list')->andReturn('Cat 1 > Cat 2');
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');

		$this->assertTrue($analytics->googleTrackingStartCheckout() !== '');
	}

	/**
	 *
	 */
	public function testFacebookTrackingStartCheckout(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$items =
		[
			[
				'product'  => 1,
                'offer'    => ['name' => 'Foobar', 'sale_price' => 19.95],
                'quantity' => 1,
			],
			[
				'product'  => 2,
                'offer'    => ['name' => 'Foobar', 'sale_price' => 19.95],
                'quantity' => 2,
			],
		];

		$ecommerce->shouldReceive('cart')->andReturn($cart);
		$cart->shouldReceive('items')->andReturn($items);
		$cart->shouldReceive('subTotal')->andReturn(29.95);
		$cart->shouldReceive('shippingCost')->andReturn(9.95);

		$this->assertTrue($analytics->facebookTrackingStartCheckout() !== '');
	}

	/**
	 *
	 */
	public function testGoogleTrackCheckoutComplete(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$order =
		[
			'id'                => 1,
			'user_id'  	        => 1,
			'bt_transaction_id' => 'bt-transaction-id',
			'shipping_id'       => 1,
			'date'              => time(),
			'status'            => 'shipped',
			'shipped'           => 1,
			'shipped_date'      => time(),
			'tracking_code'     => 'tracking-code',
			'eta'               => time(),
			'card_type'         => 'visa',
			'card_last_four'    => '4354',
			'card_expiry'       => '12/22',
			'items'             =>
			[[
				'id'           => 1,
				'name'         => 'Product Name',
				'user_id'  	   => 1,
				'product_id'   => 13,
				'offer'        => 'sku-33',
				'quantity'     => 1,
				'price'        => 19.95,
				'sale_price'   => 9.95,
			]],
			'sub_total'         => '19.95',
			'shipping_costs'    => '9.95',
			'coupon'            => '',
			'total'             => '29.95',
		];

		$query->shouldReceive('the_categories_list')->andReturn('Cat 1 > Cat 2');
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');

		$this->assertTrue($analytics->googleTrackCheckoutComplete($order) !== '');
	}

	/**
	 *
	 */
	public function testFacebookTrackCheckoutComplete(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$order =
		[
			'id'                => 1,
			'user_id'  	        => 1,
			'bt_transaction_id' => 'bt-transaction-id',
			'shipping_id'       => 1,
			'date'              => time(),
			'status'            => 'shipped',
			'shipped'           => 1,
			'shipped_date'      => time(),
			'tracking_code'     => 'tracking-code',
			'eta'               => time(),
			'card_type'         => 'visa',
			'card_last_four'    => '4354',
			'card_expiry'       => '12/22',
			'items'             =>
			[[
				'id'           => 1,
				'name'         => 'Product Name',
				'user_id'  	   => 1,
				'product_id'   => 13,
				'offer'        => 'sku-33',
				'quantity'     => 1,
				'price'        => 19.95,
				'sale_price'   => 9.95,
			]],
			'sub_total'         => '19.95',
			'shipping_costs'    => '9.95',
			'coupon'            => '',
			'total'             => '29.95',
		];

		$this->assertTrue($analytics->facebookTrackCheckoutComplete($order) !== '');
	}

	/**
	 *
	 */
	private function getMocks()
	{
		$analytics  = new Analytics(true, 'ga-id', true, 'gads-id', 'gads-conversion-id', true, 'fb-pixel-id');
		$gatekeeper = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user       = $this->mock('\kanso\cms\wrappers\User');
		$ecommerce  = $this->mock('\kanso\cms\ecommerce\Ecommerce');
		$products   = $this->mock('\kanso\cms\ecommerce\Products');
		$cart       = $this->mock('\kanso\cms\ecommerce\Cart');
		$query      = $this->mock('\kanso\cms\query\Query');
		$config     = $this->mock('\kanso\framework\config\Config');

		$analytics->Config     = $config;
		$analytics->Query      = $query;
		$analytics->Ecommerce  = $ecommerce;
		$analytics->Gatekeeper = $gatekeeper;

		$user->id = 1;
		$user->name  = 'John Doe';
		$user->email = 'foo@bar.com';

		return
		[
			'analytics'   => $analytics,
			'gatekeeper'  => $gatekeeper,
			'user'        => $user,
			'ecommerce'   => $ecommerce,
			'products'    => $products,
			'query'       => $query,
			'config'      => $config,
			'cart'        => $cart,
		];
	}

}
