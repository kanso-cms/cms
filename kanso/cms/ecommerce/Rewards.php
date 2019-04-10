<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use InvalidArgumentException;
use kanso\framework\utility\Str;

/**
 * Green Club manager utility class.
 *
 * @author Joe J. Howard
 */
class Rewards extends UtilityBase
{
    /**
     * 1 Dollar = x loyalty points.
     *
     * @var int|float
     */
    private $dollarsToPoints;

    /**
     * 100 loyalty point = x% discount.
     *
     * @var int|float
     */
    private $pointsToDiscount;

    /**
     * Constructor.
     *
     * @access public
     * @param int|float $dollarsToPoints  1 Dollar = x loyalty points (optional) (default 0.5)
     * @param int|float $pointsToDiscount 100 loyalty point = x% discount (optional) (default 10)
     */
    public function __construct($dollarsToPoints = 0.5, $pointsToDiscount = 10)
    {
    	$this->dollarsToPoints = $dollarsToPoints;

    	$this->pointsToDiscount = $pointsToDiscount;
    }

	/**
	 * Get user's un-used coupons.
	 *
	 * @access public
	 * @param  int                      $userId user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 * @return array
	 */
	public function coupons(int $userId = null): array
	{
		if (!$userId && !$this->Gatekeeper->isLoggedIn())
		{
			throw new InvalidArgumentException('A user id was not provided and the current user is not logged in.');
		}

		$userId = !$userId ? $this->Gatekeeper->getUser()->id : $userId;

		return $this->sql()->SELECT('*')->FROM('loyalty_coupons')->WHERE('user_id', '=', $userId)->ORDER_BY('date', 'DESC')->FIND_ALL();
	}

	/**
	 * Get users loyalty points balance.
	 *
	 * @access public
	 * @param  int                      $userId user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 * @return int
	 */
	public function points(int $userId = null): int
	{
		$points  = 0;

		$history = $this->history($userId);

		foreach ($history as $event)
		{
			if ($event['points_add'] > 0)
			{
				$points += $event['points_add'];
			}
			elseif ($event['points_minus'] > 0)
			{
				$points -= $event['points_minus'];
			}
		}

		return $points;
	}

	/**
	 * Get user lifetime loyalty points.
	 *
	 * @access public
	 * @param  int                      $userId user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 * @return int
	 */
	public function lifetimePoints(int $userId = null): int
	{
		$points  = 0;

		$history = $this->history($userId);

		foreach ($history as $event)
		{
			if ($event['points_add'] > 0)
			{
				$points += $event['points_add'];
			}
		}

		return $points;
	}

	/**
	 * Get user loyalty redemption history.
	 *
	 * @access public
	 * @param  int                      $userId user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 * @return array
	 */
	public function history(int $userId = null): array
	{
		if (!$userId && !$this->Gatekeeper->isLoggedIn())
		{
			throw new InvalidArgumentException('A user id was not provided and the current user is not logged in.');
		}

		$userId  = !$userId ? $this->Gatekeeper->getUser()->id : $userId;
		$balance = 0;
		$history = $this->sql()->SELECT('*')->FROM('loyalty_points')->WHERE('user_id', '=', $userId)->ORDER_BY('date', 'ASC')->FIND_ALL();

		foreach ($history as $i => $event)
		{
			if ($event['points_add'] > 0)
			{
				$balance += $event['points_add'];
			}
			elseif ($event['points_minus'] > 0)
			{
				$balance -= $event['points_minus'];
			}

			$history[$i]['balance'] = $balance;
		}

		return $history;
	}

	/**
	 * Add loyalty points to user's account.
	 *
	 * @access public
	 * @param  int                      $points      How many points to add
	 * @param  string                   $description Description of event
	 * @param  int                      $userId      user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 */
	public function addPoints(int $points, string $description, int $userId = null)
	{
		if (!$userId && !$this->Gatekeeper->isLoggedIn())
		{
			throw new InvalidArgumentException('A user id was not provided and the current user is not logged in.');
		}

		$row =
		[
			'user_id'     => !$userId ? $this->Gatekeeper->getUser()->id : $userId,
			'description' => $description,
			'date'        => time(),
			'points_add'  => $points,
		];

		return $this->sql()->INSERT_INTO('loyalty_points')->VALUES($row)->QUERY();
	}

	/**
	 * Add loyalty points.
	 *
	 * @access public
	 * @param  int                      $points      How many points to add
	 * @param  string                   $description Description of event
	 * @param  int                      $userId      user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 */
	public function minusPoints(int $points, string $description, int $userId = null)
	{
		if (!$userId && !$this->Gatekeeper->isLoggedIn())
		{
			throw new InvalidArgumentException('A user id was not provided and the current user is not logged in.');
		}

		$row =
		[
			'user_id'      => !$userId ? $this->Gatekeeper->getUser()->id : $userId,
			'description'  => $description,
			'date'         => time(),
			'points_minus' => $points,
		];

		return $this->sql()->INSERT_INTO('loyalty_points')->VALUES($row)->QUERY();
	}

	/**
	 * Redeem a coupon. Returns one-time coupon code.
	 *
	 * @access public
	 * @param  string                   $name        Name of the coupon
	 * @param  string                   $description Description of event
	 * @param  int                      $discount    Discount percentage of coupon
	 * @param  int                      $points      Points cost of coupon redemption
	 * @param  int                      $userId      user_id (optional) (default null)
	 * @throws InvalidArgumentException If current user is not logged in and $userId is not provided
	 * @return string
	 */
	public function createCoupon(string $name, string $description, int $discount, int $points, int $userId = null)
	{
		if (!$userId && !$this->Gatekeeper->isLoggedIn())
		{
			throw new InvalidArgumentException('A user id was not provided and the current user is not logged in.');
		}

		$userId  = !$userId ? $this->Gatekeeper->getUser()->id : $userId;
		$code    = strtoupper(Str::random(8, Str::ALNUM));

		$this->minusPoints($points, $discount . '% Off Coupon Redemption - CODE: ' . $code, $userId);

		$row =
		[
			'user_id'      => $userId,
			'name'         => $name,
			'description'  => $description,
			'discount'     => $discount,
			'code'         => $code,
			'date'         => time(),
			'used'         => false,
		];

		$this->sql()->INSERT_INTO('loyalty_coupons')->VALUES($row)->QUERY();

		return $code;
	}

	/**
	 * Calculate points earned from money spent.
	 *
	 * @access public
	 * @param  float $spend How much did a user spend
	 * @return int
	 */
	public function calcPoints(float $spend): int
	{
		// e.g $1 = 1.5 points
		return intval(round($spend * $this->dollarsToPoints));
	}

	/**
	 * Calculate discount from points.
	 *
	 * @access public
	 * @param  float $spend How much did a user spend
	 * @return int
	 */
	public function calcDiscount(int $points): int
	{
		// E.g. 100 points = 10%
		return intval(round($points / $this->pointsToDiscount));
	}
}
