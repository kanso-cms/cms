<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\framework\ioc\Container;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class RewardsTest extends TestCase
{
	/**
	 *
	 */
	public function testCoupons(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected = [['row1'], ['row2']];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'DESC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->coupons() === $expected);
	}

	/**
	 *
	 */
	public function testCouponsLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected = [['row1'], ['row2']];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 15)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'DESC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->coupons(15) === $expected);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCouponsFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->coupons();
	}

	/**
	 *
	 */
	public function testPoints(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => time(),
				'points_add'    => '',
				'points_minus'  => 50,
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->points() === 0);
	}

	/**
	 *
	 */
	public function testPointsById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => time(),
				'points_add'    => '',
				'points_minus'  => 50,
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 15)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->points(15) === 0);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testPointsFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->points();
	}

	/**
	 *
	 */
	public function testLifetimePoints(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 3,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => time(),
				'points_add'    => '',
				'points_minus'  => 50,
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->lifetimePoints() === 100);
	}

	/**
	 *
	 */
	public function testLifetimePointsById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => time(),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 3,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => time(),
				'points_add'    => '',
				'points_minus'  => 50,
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 15)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($expected);

		$this->assertTrue($rewards->lifetimePoints(15) === 100);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testLifetimePointsFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->lifetimePoints();
	}

	/**
	 *
	 */
	public function testHistory(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'           => 1,
			    'user_id'      => 1,
			    'description'  => 'Added 50 points',
			    'date'         => strtotime('+1 week'),
			    'points_add'   => 50,
			    'points_minus' => '',
			    'balance'      => 50,
			],
			[
				'id'           => 2,
			    'user_id'      => 1,
			    'description'  => 'Added 50 points',
			    'date'         => strtotime('+2 weeks'),
			    'points_add'   => '',
			    'points_minus' => 50,
			    'balance'      => 0,

			],
			[
				'id'           => 3,
			    'user_id'      => 1,
			    'description'  => 'Removed 50 points',
			    'date'         => strtotime('+3 weeks'),
			    'points_add'   => 25,
			    'points_minus' => '',
			    'balance'      => 25,
			],
		];

		$dbResponse =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => strtotime('+1 week'),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => strtotime('+2 weeks'),
				'points_add'    => '',
				'points_minus'  => 50,
			],
			[
				'id'            => 3,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => strtotime('+3 weeks'),
				'points_add'    => 25,
				'points_minus'  => '',
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($dbResponse);

		$this->assertTrue($rewards->history() === $expected);
	}

	/**
	 *
	 */
	public function testHistoryById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$expected =
		[
			[
				'id'           => 1,
			    'user_id'      => 1,
			    'description'  => 'Added 50 points',
			    'date'         => strtotime('+1 week'),
			    'points_add'   => 50,
			    'points_minus' => '',
			    'balance'      => 50,
			],
			[
				'id'           => 2,
			    'user_id'      => 1,
			    'description'  => 'Added 50 points',
			    'date'         => strtotime('+2 weeks'),
			    'points_add'   => '',
			    'points_minus' => 50,
			    'balance'      => 0,

			],
			[
				'id'           => 3,
			    'user_id'      => 1,
			    'description'  => 'Removed 50 points',
			    'date'         => strtotime('+3 weeks'),
			    'points_add'   => 25,
			    'points_minus' => '',
			    'balance'      => 25,
			],
		];

		$dbResponse =
		[
			[
				'id'            => 1,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => strtotime('+1 week'),
				'points_add'    => 50,
				'points_minus'  => '',
			],
			[
				'id'            => 2,
				'user_id'  	    => 1,
				'description'   => 'Added 50 points',
				'date'          => strtotime('+2 weeks'),
				'points_add'    => '',
				'points_minus'  => 50,
			],
			[
				'id'            => 3,
				'user_id'  	    => 1,
				'description'   => 'Removed 50 points',
				'date'          => strtotime('+3 weeks'),
				'points_add'    => 25,
				'points_minus'  => '',
			],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('user_id', '=', 15)->times(1)->andReturn($sql);
		$sql->shouldReceive('ORDER_BY')->with('date', 'ASC')->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn($dbResponse);

		$this->assertTrue($rewards->history(15) === $expected);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testHistoryFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->history();
	}

	/**
	 *
	 */
	public function testAddPoints(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$dbRow =
		[
			'user_id'     => 1,
			'description' => 'Added 50 points',
			'date'        => time(),
			'points_add'  => 50,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($dbRow)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$this->assertTrue($rewards->addPoints(50, 'Added 50 points'));
	}

	/**
	 *
	 */
	public function testAddPointsById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$dbRow =
		[
			'user_id'     => 15,
			'description' => 'Added 50 points',
			'date'        => time(),
			'points_add'  => 50,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($dbRow)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$this->assertTrue($rewards->addPoints(50, 'Added 50 points', 15));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddPointsFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->addPoints(50, 'Added 50 points');
	}

	/**
	 *
	 */
	public function testMinuePoints(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$dbRow =
		[
			'user_id'     => 1,
			'description' => 'Used 50 points',
			'date'        => time(),
			'points_minus'  => 50,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($dbRow)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$this->assertTrue($rewards->minusPoints(50, 'Used 50 points'));
	}

	/**
	 *
	 */
	public function testMinuePointsById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$dbRow =
		[
			'user_id'     => 15,
			'description' => 'Used 50 points',
			'date'        => time(),
			'points_minus'  => 50,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($dbRow)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$this->assertTrue($rewards->minusPoints(50, 'Used 50 points', 15));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testMinuePointsFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->minusPoints(50, 'Used 50 points');
	}

	/**
	 *
	 */
	public function testCreateCoupon(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$response = $rewards->createCoupon('10% Off Coupon', 'Coupon description', 10, 50);

		$this->assertTrue(is_string($response) && !empty($response));
	}

	/**
	 *
	 */
	public function testCreateCouponById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$rewards->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('INSERT_INTO')->with('loyalty_coupons')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$sql->shouldReceive('INSERT_INTO')->with('loyalty_points')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->andReturn(1);

		$response = $rewards->createCoupon('10% Off Coupon', 'Coupon description', 10, 50, 15);

		$this->assertTrue(is_string($response) && !empty($response));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCreateCouponFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$rewards->createCoupon('10% Off Coupon', 'Coupon description', 10, 50);
	}

	/**
	 *
	 */
	private function getMocks()
	{
		$rewards     = $this->mock('\kanso\cms\ecommerce\Rewards')->makePartial();
		$gatekeeper  = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user        = $this->mock('\kanso\cms\wrappers\User');
		$sql         = $this->sqlBuilderMocks();
		$user->id    = 1;
		$user->email = 'foo@bar.com';
		$user->name  = 'foo bar';

		$rewards->shouldAllowMockingProtectedMethods();
		$rewards->Gatekeeper = $gatekeeper;

		return
		[
			'rewards'     => $rewards,
			'sql'         => $sql,
			'gatekeeper'  => $gatekeeper,
			'user'        => $user,
		];
	}

	/**
	 *
	 */
	private function sqlBuilderMocks()
	{
		$container  = Container::instance();
		$database   = $this->mock('\kanso\framework\database\Database');
		$connection = $this->mock('\kanso\framework\database\connection\Connection');
		$builder    = $this->mock('\kanso\framework\database\query\Builder');

		$container->setInstance('Database', $database);

		$database->shouldReceive('connection')->andReturn($connection);
		$connection->shouldReceive('builder')->andReturn($builder);

		return $builder;
	}

}
