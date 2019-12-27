<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\cms\ecommerce\Checkout;
use kanso\framework\ioc\Container;
use kanso\framework\validator\ValidatorFactory;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CheckoutTest extends TestCase
{
	/**
	 *
	 */
	public function testPayment(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$args =
		[
			'shipping_use_new_address'  => true,
		    'shipping_save_address'     => false,
		    //'shipping_existing_address' => '1',
		    'shipping_first_name'       => 'John',
		    'shipping_last_name'        => 'Doe',
		    'shipping_email'            => 'test@example.com',
		    'shipping_address_1'        => '300 City Rd',
		    'shipping_address_2'        => 'Unit 52',
		    'shipping_suburb'           => 'Melbourne',
		    'shipping_zip'              => '3000',
		    'shipping_state'            => 'Victoria',
		    'shipping_country'          => 'Australia',
		    'shipping_phone'            => '0400223243',

		    'create_account'            => false,
		    //'password'                  => 'password1',
		    'apply_coupon'              => false,
		    //'coupon'                    => 'SPECIAL_10',

		    'billing_use_new_card'      => true,
		    'billing_save_card_info'    => false,
		    //'billing_existing_card'     => '1',
		    'billing_card_last_four'    => '4245',
		    'billing_card_type'         => 'visa',
		    'billing_card_name'         => 'John Doe',
		    'billing_card_mm'           => '04',
		    'billing_card_yy'           => '26',
		    'billing_method_nonce'      => 'fake-valid-nonce',
		];

		// Initial validation
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$ecommerce->shouldReceive('braintree')->andReturn($braintree);
		$braintree->shouldReceive('customer')->andReturn(false);

		// Cart
		$ecommerce->shouldReceive('cart')->andReturn($cart);
		$cart->shouldReceive('subTotal')->andReturn(19.95);
		$cart->shouldReceive('shippingCost')->andReturn(9.95);
		$cart->shouldReceive('gst')->andReturn(1.995);
		$cart->shouldReceive('items')->andReturn($this->cartItems());

		// BT transaction
		$saleArgs = $this->saleArgs();
		$query->shouldReceive('the_title')->with(1)->times(1)->andReturn('Product 1 Title');
		$query->shouldReceive('the_title')->with(2)->times(1)->andReturn('Product 2 Title');
		$braintree->shouldReceive('transaction')->with($saleArgs)->andReturn($btSuccess);

		// DB shipping
		$sql->shouldReceive('INSERT_INTO')->with('shipping_addresses')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($this->shippingRow())->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);
		$sql->shouldReceive('connectionHandler')->andReturn($connectionHandler);
		$connectionHandler->shouldReceive('lastInsertId')->andReturn(2);

		// DB transaction
		$sql->shouldReceive('INSERT_INTO')->with('transactions')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);

		// // Emails
		$request->shouldReceive('environment')->andReturn($environment);
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');
		$config->shouldReceive('get')->with('ecommerce.confirmation_email')->andReturn('orders@foo.com');
		$email->shouldReceive('preset')->andReturn('html email string');
		$email->shouldReceive('send')->times(2);

		// CRM/Session
		$cart->shouldReceive('clear');
		$crm->shouldReceive('visitor')->andReturn($visitor);
		$visitor->shouldReceive('save');
		$session->shouldReceive('put')->with('checkout-token', 'transaction-id');

		$checkout->payment($args);
	}

	/**
	 *
	 */
	public function testPaymentCreateAccount(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$args =
		[
			'shipping_use_new_address'  => true,
		    'shipping_save_address'     => false,
		    //'shipping_existing_address' => '1',
		    'shipping_first_name'       => 'John',
		    'shipping_last_name'        => 'Doe',
		    'shipping_email'            => 'test@example.com',
		    'shipping_address_1'        => '300 City Rd',
		    'shipping_address_2'        => 'Unit 52',
		    'shipping_suburb'           => 'Melbourne',
		    'shipping_zip'              => '3000',
		    'shipping_state'            => 'Victoria',
		    'shipping_country'          => 'Australia',
		    'shipping_phone'            => '0400223243',

		    'create_account'            => true,
		    'password'                  => 'password1',
		    'apply_coupon'              => false,
		    //'coupon'                    => 'SPECIAL_10',

		    'billing_use_new_card'      => true,
		    'billing_save_card_info'    => false,
		    //'billing_existing_card'     => '1',
		    'billing_card_last_four'    => '4245',
		    'billing_card_type'         => 'visa',
		    'billing_card_name'         => 'John Doe',
		    'billing_card_mm'           => '04',
		    'billing_card_yy'           => '26',
		    'billing_method_nonce'      => 'fake-valid-nonce',
		];

		// Initial validation
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$ecommerce->shouldReceive('braintree')->andReturn($braintree);
		$braintree->shouldReceive('customer')->andReturn(false);

		// Cart
		$ecommerce->shouldReceive('cart')->andReturn($cart);
		$cart->shouldReceive('subTotal')->andReturn(19.95);
		$cart->shouldReceive('shippingCost')->andReturn(9.95);
		$cart->shouldReceive('gst')->andReturn(1.995);
		$cart->shouldReceive('items')->andReturn($this->cartItems());

		// User creation
		$userManager->shouldReceive('byEmail')->with('test@example.com')->andReturn(false);
		$userManager->shouldReceive('create')->andReturn($user);
		$user->shouldReceive('save');

		// BT transaction
		$saleArgs = $this->saleArgs();
		$saleArgs['customer'] = ['id' => 1];
		$query->shouldReceive('the_title')->with(1)->times(1)->andReturn('Product 1 Title');
		$query->shouldReceive('the_title')->with(2)->times(1)->andReturn('Product 2 Title');
		$braintree->shouldReceive('transaction')->with($saleArgs)->andReturn($btSuccess);

		// DB shipping
		$sql->shouldReceive('INSERT_INTO')->with('shipping_addresses')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->with($this->shippingRow())->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);
		$sql->shouldReceive('connectionHandler')->andReturn($connectionHandler);
		$connectionHandler->shouldReceive('lastInsertId')->andReturn(2);

		// DB transaction
		$sql->shouldReceive('INSERT_INTO')->with('transactions')->times(1)->andReturn($sql);
		$sql->shouldReceive('VALUES')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);

		// // Emails
		$request->shouldReceive('environment')->andReturn($environment);
		$config->shouldReceive('get')->with('cms.site_title')->andReturn('CMS Title');
		$config->shouldReceive('get')->with('ecommerce.confirmation_email')->andReturn('orders@foo.com');
		$email->shouldReceive('preset')->andReturn('html email string');
		$email->shouldReceive('send')->times(2);

		// CRM/Session
		$gatekeeper->shouldReceive('login')->andReturn(true);
		$crm->shouldReceive('login')->andReturn(true);
		$cart->shouldReceive('clear');
		$crm->shouldReceive('visitor')->andReturn($visitor);
		$visitor->shouldReceive('save');
		$session->shouldReceive('put')->with('checkout-token', 'transaction-id');

		// Rewards
		$ecommerce->shouldReceive('rewards')->andReturn($rewards);
		$rewards->shouldReceive('addPoints');
		$rewards->shouldReceive('calcPoints');

		$checkout->payment($args);
	}

	private function shippingRow()
	{
		return
		[
            'email'            => 'test@example.com',
            'first_name'       => 'John',
            'last_name'        => 'Doe',
            'street_address_1' => '300 City Rd',
            'street_address_2' => 'Unit 52',
            'suburb'           => 'Melbourne',
            'zip_code'         => '3000',
            'state'            => 'Victoria',
            'country'          => 'Australia',
            'telephone'        => '0400223243',
		];
	}

	private function saleArgs()
	{
		return
		[
			'amount'  => 29.9,
            'options' =>
            [
                'storeInVaultOnSuccess' => true,
                'submitForSettlement'   => true,
            ],
            'paymentMethodNonce' => 'fake-valid-nonce',
		];
	}
	private function cartItems()
	{
		return
		[
			[
				'product'  => 1,
				'quantity' => 1,
				'offer'    =>
				[
					'offer_id'   => 'sku-1',
					'name'       => 'Product 1',
					'price'      => 11.95,
					'sale_price' => 9.975,
				],
			],
			[
				'product'  => 2,
				'quantity' => 1,
				'offer'    =>
				[
					'offer_id'   => 'sku-2',
					'name'       => 'Product 2',
					'price'      => 11.95,
					'sale_price' => 9.975,
				],
			],
		];
	}

	/**
	 *
	 */
	private function getMocks(): array
	{
		$container         = Container::instance();
		$checkout          = new Checkout;
		$ecommerce         = $this->mock('\kanso\cms\ecommerce\Ecommerce');
		$braintree         = $this->mock('\kanso\cms\ecommerce\Braintree');
		$coupons           = $this->mock('\kanso\cms\ecommerce\Coupons');
		$cart              = $this->mock('\kanso\cms\ecommerce\Cart');
		$rewards           = $this->mock('\kanso\cms\ecommerce\Rewards');
		$btSuccess         = $this->mock('\Braintree\Result\Successful');
		$btError           = $this->mock('\Braintree\Result\Error');
		$transaction       = $this->mock('\Braintree\Transaction');
		$crm               = $this->mock('\kanso\cms\crm\Crm');
		$request           = $this->mock('\kanso\framework\http\request\Request');
		$environment       = $this->mock('\kanso\framework\http\request\Environment');
		$session           = $this->mock('\kanso\framework\http\session\Session');
		$config            = $this->mock('\kanso\framework\config\Config');
		$fileSystem        = $this->mock('\kanso\framework\file\Filesystem');
		$connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');
		$validator         = new ValidatorFactory($container);
		$gatekeeper        = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user              = $this->mock('\kanso\cms\wrappers\User');
		$visitor           = $this->mock('\kanso\cms\wrappers\Visitor');
		$email             = $this->mock('\kanso\cms\email\Email');
		$userManager       = $this->mock('\kanso\cms\wrappers\managers\UserManager');
		$query             = $this->mock('\kanso\cms\query\Query');

		$checkout->Ecommerce    = $ecommerce;
		$checkout->Crm          = $crm;
		$checkout->Request      = $request;
		$checkout->Session      = $session;
		$checkout->Config       = $config;
		$checkout->Filesystem   = $fileSystem;
		$checkout->Validator    = $validator;
		$checkout->Gatekeeper   = $gatekeeper;
		$checkout->Email        = $email;
		$checkout->UserManager  = $userManager;
		$checkout->Query        = $query;
		$transaction->id        = 'transaction-id';
		$btSuccess->transaction = $transaction;
		$environment->DOMAIN_NAME = 'foo.com';

		$user->id    = 1;
		$user->email = 'foo@bar.com';
		$user->name  = 'foo bar';

		return
		[
			'checkout'    => $checkout,
			'ecommerce'   => $ecommerce,
			'braintree'   => $braintree,
			'coupons'     => $coupons,
			'cart'        => $cart,
			'rewards'     => $rewards,
			'btSuccess'   => $btSuccess,
			'btError'     => $btError,
			'transaction' => $transaction,
			'crm'         => $crm,
			'request'     => $request,
			'environment' => $environment,
			'session'     => $session,
			'config'      => $config,
			'fileSystem'  => $fileSystem,
			'validator'   => $validator,
			'gatekeeper'  => $gatekeeper,
			'user'        => $user,
			'visitor'     => $visitor,
			'email'       => $email,
			'userManager' => $userManager,
			'query'       => $query,
			'sql'         => $this->sqlBuilderMocks(),
			'connectionHandler' => $connectionHandler,
		];
	}

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
