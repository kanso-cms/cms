<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Exception;

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
     * Braintree customer object.
     *
     * @var \Braintree\Customer|null
     */
    private $btCustomer;

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
    public function token(): string
    {
        $this->configure();

        if ($this->Gatekeeper->isLoggedIn() && $this->btCustomer)
        {
            return $this->gateway->clientToken()->generate(['customerId' => $this->btCustomer->id]);
        }

        return $this->gateway->clientToken()->generate();
    }

    /**
     * Make a transaction.
     *
     * @param  array                                                $sale Transaction configuration
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
     * @param  int                                                  $cardId The card id from our database
     * @param  int                                                  $userId The user id
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
     * @param  int|null $id User id from the database (optional) (default null)
     * @return array
     */
    public function cards(int $id = null): array
    {
        $this->configure();

        if (!$id && !$this->Gatekeeper->isLoggedIn())
        {
            return [];
        }

        $cards  = [];
        $id     = !$id ? $this->Gatekeeper->getUser()->id : $id;
        $tokens = $this->sql()->SELECT('*')->FROM('payment_tokens')->WHERE('user_id', '=', $id)->FIND_ALL();

        if ($tokens)
        {
            foreach ($tokens as $row)
            {
                $paymentMethod = $this->gateway->paymentMethod()->find($row['token']);

                if ($paymentMethod instanceof NotFound)
                {
                    continue;
                }

                $paymentMethod->id = $row['id'];

                $cards[] = $paymentMethod;
            }
        }

        return $cards;
    }

    /**
     * Get a customer by id or the currently logged in user.
     *
     * @param  int|null                  $id User id from the database (optional) (default null)
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
     * @param  int|null                  $id User id from the database (optional) (default null)
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

                $this->getBtCustomer();
            }

            $this->btConfigured = true;
        }
    }

    /**
     * Find braintree customer.
     *
     * @return \Braintree\Result\Error|\Braintree\Result\Successful|null
     */
    private function getBtCustomer()
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            try
            {
                $this->btCustomer = $this->gateway->customer()->find($this->Gatekeeper->getUser()->id);
            }
            catch(Exception $e)
            {
                $this->btCustomer = null;
            }
        }

        return $this->btCustomer;
    }
}
