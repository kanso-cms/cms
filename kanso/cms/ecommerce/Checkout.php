<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use InvalidArgumentException;
use kanso\cms\wrappers\managers\UserManager;
use kanso\framework\utility\Str;

/**
 * Checkout processing utility.
 *
 * @author Joe J. Howard
 */
class Checkout extends UtilityBase
{
    /**
     * Status code for empty cart.
     *
     * @var int
     */
    const EMPTY_CART = 100;

    /**
     * Status code for user exists.
     *
     * @var int
     */
    const USER_EXISTS = 200;

    /**
     * Status code for non existent coupon.
     *
     * @var int
     */
    const COUPON_INVALID = 300;

    /**
     * Status code for used coupon.
     *
     * @var int
     */
    const COUPON_USED = 400;

    /**
     * Status code for ambiguous gateway error.
     *
     * @var int
     */
    const GATEWAY_ERROR = 500;

    /**
     * Status code for credit card error.
     *
     * @var int
     */
    const CARD_ERROR = 600;

    /**
     * Status code for credit card declined.
     *
     * @var int
     */
    const CARD_DECLINED = 700;

    /**
     * Status code for successful transaction exists.
     *
     * @var int
     */
    const SUCCESS = 800;

    /**
     * Successful transaction id.
     *
     * @var string|null
     */
    private $transactionId;

    /**
     * Error message for failed transactions.
     *
     * @var string|null
     */
    private $errorMessage;

    /**
     * Handle checkout.
     *
     * @param  array                    $options Array of configuration options
     * @throws InvalidArgumentException
     * @return int
     * @example
    ['shipping_use_new_address' => false,
    'shipping_save_address'     => false,
    'shipping_existing_address' => '1',
    'shipping_first_name'       => 'John',
    'shipping_last_name'        => 'Doe',
    'shipping_email'            => 'test@example.com',
    'shipping_address_1'        => '300 City Rd',
    'shipping_suburb'           => 'Melbourne',
    'shipping_zip'              => '3000',
    'shipping_state'            => 'Victoria',
    'shipping_country'          => 'Australia',
    'shipping_phone'            => '0400223243',

    'create_account'            => false,
    'password'                  => 'password1',
    'apply_coupon'              => false,
    'coupon'                    => 'SPECIAL_10',

    'billing_use_new_card'      => false,
    'billing_save_card_info'    => false,
    'billing_existing_card'     => '1',
    'billing_card_last_four'    => '4245',
    'billing_card_type'         => 'visa',
    'billing_card_name'         => 'John Doe',
    'billing_card_mm'           => '04',
    'billing_card_yy'           => '26',
    'billing_method_nonce'      => 'fake-valid-nonce',]
     */
    public function payment(array $options)
    {
        $this->transactionId = null;

        $this->errorMessage = null;

        // Validate and sanitize the configuration options
        $options = $this->normaliseOptions($options);

        $this->validateCreditCardOptions($options);

        $this->validateAddressOptions($options);

        $couponValidation = $this->validateCouponOptions($options);

        if ($couponValidation !== true)
        {
            return $couponValidation;
        }

        $cartValidation = $this->validateCartOptions();

        if ($cartValidation !== true)
        {
            return $cartValidation;
        }

        $this->validateAccountOptions($options);

        if (!$this->validateUserExists($options))
        {
            $this->processError(self::USER_EXISTS, 'A user already exists under the provided email address.');

            return self::USER_EXISTS;
        }

        // Get the coupon
        $coupon = !empty($options['coupon']) && $options['apply_coupon'] === true ? $this->Ecommerce->coupons()->discount($options['coupon']) : false;

        // Get the shopping cart
        $cart = $this->Ecommerce->cart()->items();

        // Figure out the amount to charge
        $prices = $this->getCheckoutPrice($coupon);

        // Get the card if it was provided
        $card = $options['billing_use_new_card'] === false ? $this->Ecommerce->braintree()->findCustomerCard($options['billing_existing_card'], $this->Gatekeeper->getUser()->id) : false;

        // If the user is not logged in create an account if options say to create one
        $user        = false;
        $createdUser = false;

        if (!$this->Gatekeeper->isLoggedIn())
        {
            if ($options['create_account'] === true)
            {
                // Attempt to create the user
                $user = $this->UserManager->create($options['shipping_email'], Str::slug($options['shipping_email']), $options['password'], 'customer', true);

                // If it fails return user exists
                if ($user === UserManager::USERNAME_EXISTS || $user === UserManager::SLUG_EXISTS || $user === UserManager::EMAIL_EXISTS)
                {
                    $this->processError(self::USER_EXISTS, 'A user already exists under the provided email address.');

                    return self::USER_EXISTS;
                }

                $user->name = $options['shipping_first_name'] . ' ' . $options['shipping_last_name'];

                $user->save();

                $createdUser = true;
            }
        }
        else
        {
            $user = $this->Gatekeeper->getUser();
        }

        $userId = !$user ? null : $user->id;

        // Attempt the transaction with Braintree
        $nonceOrToken = $options['billing_use_new_card'] === false ? $card->token : $options['billing_method_nonce'];
        $transaction  = $this->processTransaction($nonceOrToken, $prices['total'], !$this->Ecommerce->braintree()->customer(), $options['billing_use_new_card'], $userId);

        // Validate the transaction
        // If the transaction fails for any reason, we need to delete the user
        // we created, if we created one
        if (!$transaction || is_int($transaction))
        {
            if ($createdUser && $user)
            {
                $user->delete();
            }

            return $transaction;
        }

        // Prep the base row for the transaction
        $transactionRow = $this->getTransactionRow($options, $this->getTransactionItems($cart), $transaction->id, $prices, $coupon, $userId);

        // Prep the shipping row
        if ($options['shipping_use_new_address'] === false)
        {
            $shippingRow = $this->findShippingAddress($options['shipping_existing_address'], $this->Gatekeeper->getUser()->id);
        }
        else
        {
            $shippingRow = $this->getShippingRow($options, $userId);

            // If the user does not want to save the address, remove the user id
            if ($options['shipping_save_address'] === false)
            {
                unset($shippingRow['user_id']);
            }

            // Save the shipping row
            $this->sql()->INSERT_INTO('shipping_addresses')->VALUES($shippingRow)->QUERY();

            $shippingRow['id'] = intval($this->sql()->connectionHandler()->lastInsertId());
        }

        // If we're using an existing card get the card details
        // since they were not submitted with the form
        if ($options['billing_use_new_card'] === false)
        {
            $transactionRow['card_type']      = $card->cardType;
            $transactionRow['card_last_four'] = $card->last4;
            $transactionRow['card_expiry']    = substr($card->expirationMonth, -2) . '/' . substr($card->expirationYear, -2);
        }

        // Insert the transaction row
        $transactionRow['shipping_id'] = $shippingRow['id'];

        $this->sql()->INSERT_INTO('transactions')->VALUES($transactionRow)->QUERY();

        // If the user elected to save their card - save it to the database
        if ($options['billing_use_new_card'] === true && $options['billing_save_card_info'] === true && $userId)
        {
            $cardRow =
            [
                'token'   => $transaction->creditCardDetails->token,
                'user_id' => $userId,
            ];

            $this->sql()->INSERT_INTO('payment_tokens')->VALUES($cardRow)->QUERY();
        }

        // Log the client in if they created an account
        if ($createdUser)
        {
            $this->Gatekeeper->login($options['shipping_email'], $options['password'], true);

            $this->Crm->login();
        }

        // Delete the items from the user's cart
        $this->Ecommerce->cart()->clear();

        // Set the coupon as used
        if ($coupon)
        {
            $this->Ecommerce->coupons()->setUsed($options['coupon'], $options['shipping_email']);
        }

        // Apply points to rewards based on transaction net price
        if ($user)
        {
            $this->Ecommerce->rewards()->addPoints($this->Ecommerce->rewards()->calcPoints($prices['sub-total']), 'Online Products Purchase ($' . number_format($prices['sub-total'], 2, '.', '') . ')');
        }

        // Send order confirmation email
        $emailOrder              = $transactionRow;
        $emailOrder['items']     = unserialize($emailOrder['items']);
        $emailOrder['shipping']  = $shippingRow;
        $emailOrder['reference'] = $transaction->id;
        $emailName               = $shippingRow['first_name'] . ' ' . $shippingRow['last_name'];
        $emailEmail              = $shippingRow['email'];

        // Send confirmation email to customer
        $this->sendConfirmationEmail($emailName, $emailEmail, $emailOrder);

        // Send confirmation email to admin
        $this->sendAdminEmail($emailEmail, $transactionRow, $shippingRow, $emailOrder['items']);

        // Mark the visitor as having made a purchase
        $visitor = $this->Crm->visitor();
        $visitor->email = $emailEmail;
        $visitor->name  = $emailName;
        $visitor->made_purchase = true;
        $visitor->save();

        // If the user checked out as a guest
        // Store their transaction id in the session
        // so they can access the confirmation page
        if (!$this->Gatekeeper->isLoggedIn() && !$createdUser)
        {
            $this->Session->put('checkout-token', $transaction->id);
        }

        $this->transactionId = $transactionRow['bt_transaction_id'];

        $this->errorMessage = null;

        return self::SUCCESS;
    }

    /**
     * Was the transaction successful?
     *
     * @return bool
     */
    public function successful(): bool
    {
        return !is_null($this->transactionId);
    }

    /**
     * Was the transaction successful?
     *
     * @return string|null
     */
    public function transactionId()
    {
        return $this->transactionId;
    }

    /**
     * Was the transaction successful?
     *
     * @return string|null
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Send order confirmation to admin.
     *
     * @param string $userEmail]  Email address of user that made order
     * @param array  $transaction Transaction row from database
     * @param array  $shipping    Shipping row from databse
     * @param array  $items       Array Of order items
     */
    private function sendAdminEmail(string $userEmail, array $transaction, array $shipping, array $items): void
    {
        $domain      = $this->Request->environment()->DOMAIN_NAME;
        $toEmail     = $this->Config->get('ecommerce.confirmation_email');
        $senderName  = $this->Config->get('cms.site_title');
        $senderEmail = 'info@' . $domain;
        $subject     = 'Order Received';
        $content     = "A new order on $domain has been received.\n\n";
        $content    .= 'Date: ' . date('l jS F Y h:i:s A') . "\n";
        $content    .= 'Price: $' . number_format($transaction['total'], 2, '.', '') . "\n";
        $content    .= 'Refernce: #' . $transaction['bt_transaction_id'] . "\n";
        $content    .= 'Email: ' . $userEmail . "\n\n";
        $content    .= "Items: \n";

        foreach($items as $item)
        {
            $content .= $item['quantity'] . 'x - ' . $item['name'] . ' - ' . $item['offer'] . "\n";
        }

        $content .= "\nShipping Details: \n";
        $content .= $shipping['first_name'] . ' ' . $shipping['last_name'] . "\n";
        $content .= $shipping['suburb'] . ' ' . $shipping['state'] . ' ' . $shipping['zip_code'] . "\n";
        $content .= 'Telephone: ' . $shipping['telephone'] . "\n";

        $this->Email->send($toEmail, $senderName, $senderEmail, $subject, $content, 'plain text');
    }

    /**
     * Send newly registered users their order confirmation.
     *
     * @param string $name  User name
     * @param string $email User email
     * @param array  $order Order details
     */
    private function sendConfirmationEmail(string $name, string $email, array $order): void
    {
        $domain  = $this->Request->environment()->DOMAIN_NAME;
        $name    = ucfirst(explode(' ', trim($name))[0]);

        $emailData =
        [
            'name'        => $name,
            'order'       => $order,
            'subject'     => 'Your Order Confirmation',
        ];

        // Email credentials
        $senderName   = $this->Config->get('cms.site_title');
        $senderEmail  = 'orders@' . $domain;
        $emailSubject = 'Your Order Confirmation';
        $emailTo      = $email;
        $emailContent = $this->Email->preset('order-confirmed', $emailData, true);

        $this->Email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
    }

    /**
     * Get the base shipping row to insert into the DB.
     *
     * @param  array       $options POST shipping details
     * @param  int|null    $userId  Current user id (optional) (default null)
     * @return array|false
     */
    private function getShippingRow(array $options, int $userId = null)
    {
        return
        [
            'user_id'          => $options['shipping_save_address'] === true ? $userId : null,
            'email'            => $options['shipping_email'],
            'first_name'       => ucfirst($options['shipping_first_name']),
            'last_name'        => ucfirst($options['shipping_last_name']),
            'street_address_1' => $options['shipping_address_1'],
            'street_address_2' => isset($options['shipping_address_2']) ? $options['shipping_address_2'] : '',
            'suburb'           => ucfirst($options['shipping_suburb']),
            'zip_code'         => $options['shipping_zip'],
            'state'            => $options['shipping_state'],
            'country'          => $options['shipping_country'],
            'telephone'        => $options['shipping_phone'],
        ];
    }

    /**
     * Get the base transaction row to insert into the DB.
     *
     * @param array    $options       Array with credit card details
     * @param array    $items         Cart item descriptions to serialize
     * @param string   $transactionId Braintree transaction id
     * @param array    $prices        Array of price breakdown
     * @param int|null $coupon        Coupon discount percentage (optional) (default null)
     * @param int|null $userId        Current user id (optional) (default null)
     *
     * @return array
     */
    private function getTransactionRow(array $options, array $items, string $transactionId, array $prices, $coupon = null, int $userId = null): array
    {
        return
        [
            'bt_transaction_id' => $transactionId,
            'user_id'           => $userId,
            'shipping_id'       => null,
            'date'              => time(),
            'status'            => 'received',
            'shipped'           => false,
            'eta'               => strtotime('+7 days'),
            'card_type'         => $options['billing_card_type'],
            'card_last_four'    => $options['billing_card_last_four'],
            'card_expiry'       => $options['billing_card_mm'] . '/' . $options['billing_card_yy'],
            'items'             => serialize($items),
            'coupon'            => $coupon,
            'sub_total'         => $prices['sub-total'],
            'shipping_costs'    => $prices['shipping-cost'],
            'total'             => $prices['total'],
        ];
    }

    /**
     * Find an existing customer's card by id.
     *
     * @param  array $cart Cart items
     * @return array
     */
    private function getTransactionItems(array $cart): array
    {
        $items = [];

        foreach ($cart as $item)
        {
            $items[] =
            [
                'product_id' => $item['product'],
                'offer_id'   => $item['offer']['offer_id'],
                'quantity'   => $item['quantity'],
                'name'       => $this->Query->the_title($item['product']),
                'offer'      => $item['offer']['name'],
                'price'      => number_format(($item['offer']['sale_price'] * $item['quantity']), 2, '.', ''),
            ];
        }

        return $items;
    }

    /**
     * Find an existing customer's card by id.
     *
     * @param  int   $addressId The address id from our database
     * @param  int   $userId    The user id
     * @return array
     */
    private function findShippingAddress(int $addressId, int $userId): array
    {
        return $this->sql()->SELECT('*')->FROM('shipping_addresses')->WHERE('id', '=', $addressId)->AND_WHERE('user_id', '=', $userId)->ROW();
    }

    /**
     * Get the price to checkout.
     *
     * @param  float|int|false $coupon The amount of the transaction
     * @return array
     */
    private function getCheckoutPrice($coupon = false): array
    {
        $subtotal     = $this->Ecommerce->cart()->subTotal();
        $shippingCost = $this->Ecommerce->cart()->shippingCost();
        $GST          = $this->Ecommerce->cart()->gst();
        $priceTotal   = $subtotal + $shippingCost;

        $response =
        [
            'sub-total'     => $subtotal,
            'shipping-cost' => $shippingCost,
            'total'         => $subtotal + $shippingCost,
        ];

        if ($coupon)
        {
            $price = $subtotal;
            $price = $price * ((100-$coupon) / 100);
            $response['total']     = round($price + $shippingCost, 2);
            $response['sub-total'] = $price;
        }

        return $response;
    }

    /**
     * Process the transaction with Braintree.
     *
     * @param  string                     $nonceOrToken   A payment method nonce or existing card token
     * @param  float                      $amount         The amount of the transaction
     * @param  bool                       $createCustomer Create a new customer
     * @param  bool                       $useNonce       Pay using a new card
     * @param  int|null                   $userId         User id to create customer from (optional) (default null)
     * @return int|\Braintree\Transaction
     */
    private function processTransaction($nonceOrToken, $amount, bool $createCustomer, $useNonce = false, int $userId = null)
    {
        $sale =
        [
            'amount'  => $amount,
            'options' =>
            [
                'storeInVaultOnSuccess' => true,
                'submitForSettlement'   => true,
            ],
        ];

        if ($useNonce)
        {
           $sale['paymentMethodNonce'] = $nonceOrToken;
        }
        else
        {
            $sale['paymentMethodToken'] = $nonceOrToken;
        }

        if ($userId)
        {
            if ($createCustomer === true)
            {
                $sale['customer'] = ['id' => $userId];
            }
            else
            {
                $sale['customerId'] = $userId;
            }
        }

        $result = $this->Ecommerce->braintree()->transaction($sale);

        if ($result->success)
        {
            return $result->transaction;
        }
        else
        {
            if (!$result->transaction)
            {
                $this->processError(self::GATEWAY_ERROR, $result->message, $result);

                return self::GATEWAY_ERROR;
            }
            if ($result->transaction->status === 'processor_declined')
            {
                $this->processError(self::CARD_ERROR, $result->message, $result);

                return self::CARD_ERROR;
            }
            elseif ($result->transaction->status === 'settlement_declined')
            {
                $this->processError(self::CARD_DECLINED, $result->message, $result);

                return self::CARD_DECLINED;
            }
        }

        $this->processError(self::GATEWAY_ERROR, $result->message, $result);

        return self::GATEWAY_ERROR;
    }

    /**
     * Validate and sanitize checkout options.
     *
     * @param  array                    $options Array of configuration options
     * @throws InvalidArgumentException
     * @return array
     */
    private function normaliseOptions(array $options): array
    {
        if (!isset($options['billing_use_new_card']) || !isset($options['shipping_use_new_address']))
        {
            throw new InvalidArgumentException('The "billing_use_new_card" and "shipping_use_new_address" fields are required.');
        }

        if (!$this->Gatekeeper->isLoggedIn() && !isset($options['create_account']))
        {
            throw new InvalidArgumentException('If user is not logged in, the "create_account" field is required.');
        }

        $rules =
        [
            'billing_use_new_card'     => ['required'],
            'shipping_use_new_address' => ['required'],
            'billing_save_card_info'   => ['required'],
            'shipping_save_address'    => ['required'],
            'apply_coupon'             => ['required'],
        ];

        $filters =
        [
            'billing_use_new_card'     => ['boolean'],
            'shipping_use_new_address' => ['boolean'],
            'billing_save_card_info'   => ['boolean'],
            'shipping_save_address'    => ['boolean'],
            'apply_coupon'             => ['boolean'],
            'coupon'                   => ['string', 'trim'],
        ];

        if (Str::bool($options['billing_use_new_card']) === true)
        {
            $rules = array_merge($rules,
            [
                'billing_card_last_four'=> ['required'],
                'billing_card_type'     => ['required'],
                'billing_card_name'     => ['required'],
                'billing_card_mm'       => ['required'],
                'billing_card_yy'       => ['required'],
                'billing_method_nonce'  => ['required'],
            ]);
            $filters = array_merge($filters,
            [
                'billing_card_last_four' => ['numeric'],
                'billing_card_type'      => ['string', 'trim'],
                'billing_card_name'      => ['string', 'trim'],
                'billing_card_mm'        => ['numeric'],
                'billing_card_yy'        => ['numeric'],
                'billing_method_nonce'   => ['string', 'trim'],
            ]);
        }
        else
        {
            $rules['billing_existing_card']   = ['required'];
            $filters['billing_existing_card'] = ['numeric'];

        }
        if (Str::bool($options['shipping_use_new_address']) === true)
        {
            $rules = array_merge($rules,
            [
                'shipping_first_name'  => ['required'],
                'shipping_last_name'   => ['required'],
                'shipping_email'       => ['required', 'email'],
                'shipping_address_1'   => ['required'],
                'shipping_suburb'      => ['required'],
                'shipping_zip'         => ['required'],
                'shipping_state'       => ['required'],
                'shipping_country'     => ['required'],
                'shipping_phone'       => ['required'],
            ]);
            $filters = array_merge($filters,
            [
                'shipping_first_name'  => ['string', 'trim'],
                'shipping_last_name'   => ['string', 'trim'],
                'shipping_email'       => ['string', 'trim', 'email'],
                'shipping_address_1'   => ['string', 'trim'],
                'shipping_address_2'   => ['string', 'trim'],
                'shipping_suburb'      => ['string', 'trim'],
                'shipping_zip'         => ['string', 'trim'],
                'shipping_state'       => ['string', 'trim'],
                'shipping_country'     => ['string', 'trim'],
                'shipping_phone'       => ['string', 'trim'],
            ]);

            if ($this->Gatekeeper->isLoggedIn())
            {
                unset($rules['shipping_email']);
            }
        }
        else
        {
            $rules['shipping_existing_address']   = ['required'];
            $filters['shipping_existing_address'] = ['numeric'];
        }

        $validator = $this->Validator->create($options, $rules, $filters);

        if (!$validator->isValid())
        {
            throw new InvalidArgumentException(implode(' ', $validator->getErrors()));
        }

        $options = $validator->filter();

        // Final sanitization
        $options['billing_use_new_card']      = Str::bool($options['billing_use_new_card']);
        $options['shipping_use_new_address']  = Str::bool($options['shipping_use_new_address']);
        $options['billing_save_card_info']    = Str::bool($options['billing_save_card_info']);
        $options['shipping_save_address']     = Str::bool($options['shipping_save_address']);
        $options['apply_coupon']              = Str::bool($options['apply_coupon']);
        $options['create_account']            = isset($options['create_account']) ? Str::bool($options['create_account']) : false;

        // If the user is logged in the 'shipping_email'
        // will not be set. So we should add it from the user's object
        if ($this->Gatekeeper->isLoggedIn())
        {
            $options['shipping_email'] = $this->Gatekeeper->getUser()->email;
        }

        return $options;
    }

    /**
     * If the user is not logged in and is creating an account
     * validate that the email address they entered does not already exist.
     *
     * @param  array $options Array of configuration options
     * @return bool
     */
    private function validateUserExists(array $options): bool
    {
        if (!$this->Gatekeeper->isLoggedIn() && $options['create_account'] === true)
        {
            $user = $this->UserManager->byEmail($options['shipping_email']);

            if ($user)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * If the user is not logged in and is creating an account
     * validate that the email address they entered does not already exist.
     *
     * @param  array                    $options Array of configuration options
     * @throws InvalidArgumentException
     */
    private function validateCreditCardOptions(array $options): void
    {
        // If the user is using an existing card,
        // validate it exists and belongs to them
        if ($options['billing_use_new_card'] === false)
        {
            if (!$this->Gatekeeper->isLoggedIn())
            {
                throw new InvalidArgumentException('Cannot use existing credit card on logged out user.');
            }

            $card = $this->Ecommerce->braintree()->findCustomerCard($options['billing_existing_card'], $this->Gatekeeper->getUser()->id);

            if (!$card)
            {
                throw new InvalidArgumentException('The existing credit card does not exist.');
            }
        }
        else
        {
            // A nonce must be provided
            if (empty($options['billing_method_nonce']))
            {
                throw new InvalidArgumentException('No credit card nonce provided.');
            }
        }
    }

    /**
     * If the user is not logged in and using an
     * existing address validate it exists and belongs to them.
     *
     * @param  array                    $options Array of configuration options
     * @throws InvalidArgumentException
     */
    private function validateAddressOptions(array $options): void
    {
        if ($options['shipping_use_new_address'] === false)
        {
            if (!$this->Gatekeeper->isLoggedIn())
            {
                throw new InvalidArgumentException('An existing address was requested but the user is not logged in.');
            }

            if (! $this->findShippingAddress($options['shipping_existing_address'], $this->Gatekeeper->getUser()->id))
            {
                throw new InvalidArgumentException('An existing address was requested but the address does not exist.');
            }
        }
    }

    /**
     * If a coupon is being used, validate it exists and is not used.
     *
     * @param  array    $options Array of configuration options
     * @return bool|int
     */
    private function validateCouponOptions(array $options)
    {
        if (!empty($options['coupon']) && $options['apply_coupon'] === true)
        {
            if (!$this->Ecommerce->coupons()->exists($options['coupon']))
            {
                $this->processError(self::COUPON_INVALID, 'The provided coupon does not exist.');

                return self::COUPON_INVALID;
            }

            if ($this->Ecommerce->coupons()->used($options['coupon'], $options['shipping_email']))
            {
                $this->processError(self::COUPON_USED, 'The provided coupon has already been used.');

                return self::COUPON_USED;
            }
        }

        return true;
    }

    /**
     * If a coupon is being used, validate it exists and is not used.
     *
     *  @return bool|int
     */
    private function validateCartOptions()
    {
        // Load the client's shopping cart
        $cart = $this->Ecommerce->cart()->items();

        // Cart must not be empty
        if (!$cart || empty($cart))
        {
            $this->processError(self::EMPTY_CART, 'The shopping cart is empty.');

            return self::EMPTY_CART;
        }

        return true;
    }

    /**
     * Validate creating an account options are correct.
     *
     * @throws InvalidArgumentException
     */
    private function validateAccountOptions(array $options): void
    {
        // If the user is creating an account, they must provide a password
        if ($options['create_account'] === true && (!isset($options['password']) || empty($options['password'])))
        {
            throw new InvalidArgumentException('Cannot create an account without provided a password.');
        }

        // If the user is creating an account, they must not be logged in
        if ($options['create_account'] === true && $this->Gatekeeper->isLoggedIn())
        {
            throw new InvalidArgumentException('Cannot create an account under logged in user.');
        }
    }

    /**
     * Process and log error.
     *
     * @param int    $code        Error code
     * @param string $message     Error message
     * @param mixed  $transaction Braintree response object (optional)
     */
    private function processError(int $code, string $message, $transaction = null): void
    {
        if ($transaction)
        {
            $this->errorMessage = $transaction->message;

            $this->logError($transaction);
        }
        else
        {
            $this->errorMessage = $message;

            $msg =
            'DATE     : ' . date('l jS \of F Y h:i:s A', time()) . "\n" .
            'DATE     : ' . $code . "\n" .
            'MESSAGE  : ' . $message . "\n\n\n";

            $this->Filesystem->appendContents(APP_DIR . '/storage/logs/transaction_errors.log', $msg);
        }
    }

    /**
     * Log a payment error.
     *
     *  @param  mixed  $transaction Braintree response object
     */
    private function logError($transaction): void
    {
        $msg =
        'DATE        : ' . date('l jS \of F Y h:i:s A', time()) . "\n" .
        'TRANSACTION : ' . var_export($transaction, true) . "\n\n\n";

        $this->Filesystem->appendContents(APP_DIR . '/storage/logs/transaction_errors.log', $msg);
    }
}
