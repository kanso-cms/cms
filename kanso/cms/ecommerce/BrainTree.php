<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use Braintree\Exception\NotFound;
use Braintree\Gateway;

/**
 * Coupon manager utility class.
 *
 * @author Joe J. Howard
 */
class BrainTree extends UtilityBase
{
    /**
     * Has braintree been configured ?
     *
     * @var bool
     */
    private $btConfigured = false;

    /**
     * Braintree customer object.
     *
     * @var \Braintree\Gateway|null
     */
    private $gateway;

    /**
     * Constructor.
     *
     * @param \Braintree\Gateway|null $gateway Braintree gateway instance (optional) (default null)
     */
    public function __construct(Gateway $gateway = null)
    {
        $this->gateway = $gateway;
    }

    /**
     * Generate and return a token for JS nonce.
     *
     * @return string
     */
    public function token()
    {
        $this->configure();

        if ($this->Gatekeeper->isLoggedIn())
        {
            return $this->gateway->clientToken()->generate(['customerId' => $this->Gatekeeper->getUser()->id]);
        }

        return $this->gateway->clientToken()->generate();
    }

    /**
     * Make a transaction.
     *
     * @param  array                                               $sale Transaction configuration
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function transaction(array $sale)
    {
        $this->configure();

        return $this->gateway->transaction()->sale($sale);
    }

    /**
     * Find an existing customer's card by id.
     *
     * @param  int                            $cardId The card id from our database
     * @param  int                            $userId The user id
     * @return \Braintree\CreditCard|\Braintree\PayPalAccount|false
     */
    public function findCustomerCard(int $cardId, int $userId)
    {
        $this->configure();

        $cardRow = $this->sql()->SELECT('*')->FROM('payment_tokens')->WHERE('id', '=', $cardId)->AND_WHERE('user_id', '=', $userId)->ROW();

        if (!$cardRow)
        {
            return false;
        }

        $card = $this->gateway->paymentMethod()->find($cardRow['token']);

        if ($card instanceof NotFound)
        {
            return false;
        }

        return $card;
    }

    /**
     * Get a user's credit cards by id or current logged in user.
     *
     * @param  int|null   $id User id from the database (optional) (default null)
     * @return array
     */
    public function cards(int $id = null)
    {
        $this->configure();

        $customer = $this->customer($id);

        if (!$customer)
        {
            return [];
        }

        return $customer->paymentMethods;
    }

    /**
     * Get a customer by id or the currently logged in user.
     *
     * @param  int|null                       $id User id from the database (optional) (default null)
     * @return \Braintree\Customer|false
     */
    public function customer(int $id = null)
    {
        $this->configure();

        if (!$id && !$this->Gatekeeper->isLoggedIn())
        {
            return false;
        }

        $id = !$id ? $this->Gatekeeper->getUser()->id : $id;

        $customer = $this->gateway->customer()->find($id);

        if ($customer instanceof NotFound)
        {
            return false;
        }

        return $customer;
    }

    /**
     * Create New Braintree customer.
     *
     * @param  int|null                       $id User id from the database (optional) (default null)
     * @return \Braintree\Customer|false
     */
    public function createCustomer(int $id = null)
    {
        $this->configure();

        if (!$id && !$this->Gatekeeper->isLoggedIn())
        {
            return false;
        }

        $id = !$id ? $this->Gatekeeper->getUser()->id : $id;

        $user = $this->sql()->SELECT('*')->FROM('users')->WHERE('id', '=', $id)->ROW();

        if (!$user)
        {
            return false;
        }

        $name = explode(' ', $user['name']);

        $result = $this->gateway->customer()->create([
            'firstName' => array_shift($name),
            'lastName'  => trim(implode(' ', $name)),
            'email'     => $user['email'],
            'id'        => $id,
        ]);

        return $result->success ? $result->customer : false;
    }

    /**
     * Create New Braintree customer.
     *
     * @param  string                         $nonce Client nonce
     * @param  int|null                       $id    User/customer id
     * @return \Braintree\PaymentMethod|false
     */
    public function addCard(string $nonce, int $id = null)
    {
        $this->configure();

        if (!$id && !$this->Gatekeeper->isLoggedIn())
        {
            return false;
        }

        $id = !$id ? $this->Gatekeeper->getUser()->id : $id;

        $result = $this->gateway->paymentMethod()->create([
            'customerId'         => $id,
            'paymentMethodNonce' => $nonce,
        ]);

        return $result->success ? $result->paymentMethod : false;
    }

    /**
     * Instantiate braintree.
     */
    private function configure(): void
    {
        if (!$this->btConfigured)
        {
            if (!$this->gateway)
            {
                $btConfig = $this->Config->get('ecommerce.braintree');

                $this->gateway = new Gateway([
                    'environment' => $btConfig['environment'],
                    'merchantId'  => $btConfig['merchant_id'],
                    'publicKey'   => $btConfig['public_key'],
                    'privateKey'  => $btConfig['private_key'],
                ]);
            }

            $this->btConfigured = true;
        }
    }
}
