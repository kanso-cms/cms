<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

/**
 * Coupon manager utility class.
 *
 * @author Joe J. Howard
 */
class Coupons extends UtilityBase
{
	/**
	 * Check if a coupon exists.
	 *
	 * @param  string $couponName The coupon name or code
	 * @return bool
	 */
	public function exists(string $couponName): bool
	{
		$couponName = strtoupper(trim($couponName));

		if (!empty($couponName))
        {
            // Find the coupon
            $coupon = $this->Config->get('ecommerce.coupons.' . $couponName);

            // Coupon may be a public coupon
            if ($coupon)
            {
               	return true;
            }

            // Coupon could be a loyalty redemption
            elseif ($this->Gatekeeper->isLoggedIn())
            {
                if ($this->sql()->SELECT('*')->FROM('loyalty_coupons')->WHERE('code', '=', $couponName)->AND_WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->ROW())
                {
                    return true;
                }
            }
        }

        return false;
	}

    /**
     * Check if a coupon exists and is used.
     *
     * @param  string $couponName The coupon name or code
     * @param  string $email      Check if a user has used this coupon by email address
     * @return bool
     */
    public function used(string $couponName, string $email = ''): bool
    {
        $couponName = strtoupper(trim($couponName));

        if (!empty($couponName))
        {
            // Find the coupon
            $coupon = $this->Config->get('ecommerce.coupons.' . $couponName);

            // Coupon may be a public coupon
            if ($coupon)
            {
                // Validate the user has not already used the coupon before
                if ($this->Gatekeeper->isLoggedIn())
                {
                    if ($this->sql()->SELECT('*')->FROM('used_public_coupons')->WHERE('coupon_name', '=', $couponName)->AND_WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->ROW())
                    {
                        return true;
                    }
                    elseif ($this->sql()->SELECT('*')->FROM('used_public_coupons')->WHERE('coupon_name', '=', $couponName)->AND_WHERE('email', '=', $this->Gatekeeper->getUser()->email)->ROW())
                    {
                        return true;
                    }
                }
                elseif ($email)
                {
                    if ($this->sql()->SELECT('*')->FROM('used_public_coupons')->WHERE('coupon_name', '=', $couponName)->AND_WHERE('email', '=', $email)->ROW())
                    {
                        return true;
                    }
                }

                return false;
            }
            // Coupon could be a loyalty redemption
            elseif ($this->Gatekeeper->isLoggedIn())
            {
                if ($this->sql()->SELECT('*')->FROM('loyalty_coupons')->WHERE('code', '=', $couponName)->AND_WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('used', '=', true)->ROW())
                {
                    return true;
                }

                return false;
            }
        }

        return true;
    }

	/**
	 * Get a coupon's discount value.
	 *
	 * @param  string          $couponName The coupon name or code
	 * @return int|float|false
	 */
	public function discount(string $couponName)
	{
		$couponName = strtoupper(trim($couponName));

        if (!empty($couponName))
        {
            // Find the coupon
            $coupon = $this->Config->get('ecommerce.coupons.' . $couponName);

            // Coupon may be a public coupon
            if ($coupon)
            {
                return $coupon;
            }

            // Coupon could be a loyalty redemption
            elseif ($this->Gatekeeper->isLoggedIn())
            {
                $coupon = $this->sql()->SELECT('*')->FROM('loyalty_coupons')->WHERE('code', '=', $couponName)->AND_WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('used', '=', false)->ROW();

                if ($coupon)
                {
                    return $coupon['discount'];
                }
            }
        }

        return false;
	}

	/**
	 * Set a coupon as used.
	 *
	 * @param  string $couponName The coupon name or code
     * @param  string $email      Email address associated with coupon (optional) (default '') 
     * @return bool 
	 */
	public function setUsed(string $couponName = '', string $email = ''): bool
	{
		$couponName = strtoupper(trim($couponName));

        if (empty($couponName))
        {
            return false;
        }

        // Find the coupon
        $coupon = $this->Config->get('ecommerce.coupons.' . $couponName);

        // Coupon may be a public coupon
        if ($coupon)
        {
            $couponRow =
            [
                'user_id'     => $this->Gatekeeper->isLoggedIn() ? $this->Gatekeeper->getUser()->id : null,
                'email'       => $email,
                'coupon_name' => $couponName,
            ];

            return boolval($this->sql()->INSERT_INTO('used_public_coupons')->VALUES($couponRow)->QUERY());
        }
        // Coupon could be a loyalty redemption
        elseif ($this->Gatekeeper->isLoggedIn())
        {
        	$coupon = $this->sql()->SELECT('*')->FROM('loyalty_coupons')->WHERE('code', '=', $couponName)->AND_WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('used', '=', false)->ROW();

        	if ($coupon)
        	{
        		$id = $coupon['id'];
        		unset($coupon['id']);
        		$coupon['used'] = true;

        		return boolval($this->sql()->UPDATE('loyalty_coupons')->SET($coupon)->WHERE('id', '=', $id)->QUERY());
        	}
        }

        return false;
	}
}
