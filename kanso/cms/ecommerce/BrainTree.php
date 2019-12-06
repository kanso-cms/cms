<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\Customer;
use Braintree\PaymentMethod;
use Braintree\Transaction;
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
    private $btConfigured;

    /**
     * Braintree customer object.
     *
     * @var \Braintree\Customer|null
     */
    private $btCustomer;

    /**
     * Generate and return a token for JS nonce.
     *
     * @return string
     */
    public function token()
    {
        // Configure BT
        $this->configure();

        if ($this->Gatekeeper->isLoggedIn())
        {
            if ($this->btCustomer)
            {
                return ClientToken::generate(['customerId' => $this->btCustomer->id]);
            }
        }

        return ClientToken::generate();
    }

    /**
     * Make a transaction
     *
     * @param  array       $sale Transaction configuration
     * @return Transaction
     */
    public function transaction(array $sale)
    {
        $this->configure();

        return Transaction::sale($sale);
    }

    /**
     * Find an existing customer's card by id.
     *
     * @param  int                 $cardId The card id from our database
     * @param  int                 $userId The user id
     * @return PaymentMethod|false
     */
    public function findCustomerCard(int $cardId, int $userId)
    {
        $this->configure();

        $cardRow = $this->sql()->SELECT('*')->FROM('payment_tokens')->WHERE('id', '=', $cardId)->AND_WHERE('user_id', '=', $userId)->ROW();

        if (!$cardRow)
        {
            return false;
        }

        $card = PaymentMethod::find($cardRow['token']);

        if ($card)
        {
            return $card;
        }

        return false;
    }

    /**
     * Get logged in user's stored credit cards from BT.
     *
     * @return array
     */
    public function cards()
    {
        $this->configure();

        $cards = [];

        if ($this->btCustomer)
        {
            $tokens = $this->sql()->SELECT('*')->FROM('payment_tokens')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->FIND_ALL();

            if ($tokens)
            {
                foreach ($tokens as $row)
                {
                    $paymentMethod = PaymentMethod::find($row['token']);

                    if ($paymentMethod)
                    {
                        $paymentMethod->id = $row['id'];

                        $cards[] = $paymentMethod;
                    }
                }
            }
        }

        return $cards;
    }

    /**
     * Get the logged in customer.
     *
     * @return Customer|null
     */
    public function customer()
    {
        $this->configure();

        return $this->btCustomer;
    }

    /**
     * Create New Braintree customer.
     *
     * @throws Exception                                            If customer couldn't be created
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function createCustomer()
    {
        $this->configure();

        $user = $this->Gatekeeper->getUser();
        $name = explode(' ', $user->name);

        $customer = Customer::create([
            'firstName' => array_shift($name),
            'lastName'  => trim(implode(' ', $name)),
            'email'     => $user->email,
            'id'        => $user->id,
        ]);

        if ($customer->success)
        {
            return $customer;
        }

        throw new Exception('Error creating new customer. The customer could not be created.');
    }

    /**
     * Create New Braintree customer.
     *
     * @return \Braintree\PaymentMethod|false
     */
    public function addCard(string $nonce)
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            $this->configure();

            // Save the card
            $paymentMethod = PaymentMethod::create([
                'customerId'         => $this->Gatekeeper->getUser()->id,
                'paymentMethodNonce' => $nonce,
            ]);

            // Validate the method
            if (!$paymentMethod->success)
            {
                return false;
            }

            return $paymentMethod;
        }

        return false;
    }

    /**
     * Instantiate braintree.
     */
    private function configure(): void
    {
        if (!$this->btConfigured)
        {
            // Configure braintree
            $btConfig = $this->Config->get('ecommerce.braintree');

            Configuration::environment($btConfig['environment']);
            Configuration::merchantId($btConfig['merchant_id']);
            Configuration::publicKey($btConfig['public_key']);
            Configuration::privateKey($btConfig['private_key']);

            $this->getBtCustomer();

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
                $this->btCustomer = Customer::find($this->Gatekeeper->getUser()->id);
            }
            catch(Exception $e)
            {
                $this->btCustomer = null;
            }
        }

        return $this->btCustomer;
    }
}
