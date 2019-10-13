<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\cms\ecommerce\Products;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class ProductsTest extends TestCase
{
	/**
	 *
	 */
	public function testOffer()
	{
		$postManager  = Mockery::mock('\kanso\cms\wrappers\managers\PostManager');
		$product      = $this->fakeProduct();
		$products     = new Products;
		$meta         =
		[
			'offers' =>
			[
				[
					'offer_id'   => 'foobar',
					'name'       => 'product name',
					'price'      => 9.95,
					'sale_price' => 4.95,
					'instock'    => true,
				],
			],
		];

		$products->PostManager = $postManager;
		$product->meta         = $meta;

		$postManager->shouldReceive('byId')->with(1)->andReturn($product);

		$this->assertEquals($meta['offers'][0], $products->offer(1, 'foobar'));
	}

	/**
	 *
	 */
	public function testOffers()
	{
		$postManager  = Mockery::mock('\kanso\cms\wrappers\managers\PostManager');
		$product      = $this->fakeProduct();
		$products     = new Products;
		$meta         =
		[
			'offers' =>
			[
				[
					'offer_id'   => 'foobar',
					'name'       => 'product name',
					'price'      => 9.95,
					'sale_price' => 4.95,
					'instock'    => true,
				],
			],
		];

		$products->PostManager = $postManager;
		$product->meta         = $meta;

		$postManager->shouldReceive('byId')->with(1)->andReturn($product);

		$this->assertEquals($meta['offers'], $products->offers(1));

	}

	private function fakeProduct()
	{
		return new fakeProduct;
	}

}

class fakeProduct
{

}
