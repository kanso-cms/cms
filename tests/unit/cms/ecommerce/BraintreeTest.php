<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\cms\ecommerce\BrainTree;
use kanso\framework\ioc\Container;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class BraintreeTest extends TestCase
{
	/**
	 *
	 */
	public function testTokenLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gateway->shouldReceive('clientToken')->andReturn($cleintToken);
		$cleintToken->shouldReceive('generate')->andReturn('nonce');
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals($bt->token(), 'nonce');
	}

	/**
	 *
	 */
	public function testTokenLoggedIn(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gateway->shouldReceive('clientToken')->andReturn($cleintToken);
		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(1)->andReturn($btCustomer);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$cleintToken->shouldReceive('generate')->with(['customerId' => 1])->andReturn('nonce');

		$this->assertEquals($bt->token(), 'nonce');
	}

	/**
	 *
	 */
	public function testTransaction(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);
		$saleArgs =
		[
			'amount' => '10.00',
			'paymentMethodNonce' => 'nonceFromTheClient',
			'options' =>
		  	[
		    	'submitForSettlement' => true,
		  	],
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gateway->shouldReceive('transaction')->andReturn($transaction);
		$transaction->shouldReceive('sale')->with($saleArgs)->andReturn($resultSuccess);

		$this->assertEquals($bt->transaction($saleArgs), $resultSuccess);
	}

	/**
	 *
	 */
	public function testFindCustomerCard(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$cardrow =
		[
			'id'      => 1,
			'user_id' => 1,
			'token'   => 'foobar',
		];

		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('payment_tokens')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($cardrow);

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('find')->with('foobar')->andReturn($paymentMethod);

		$this->assertEquals($bt->findCustomerCard(1, 1), $paymentMethod);
	}

	/**
	 *
	 */
	public function testFindCustomerCardFailDB(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('payment_tokens')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertFalse($bt->findCustomerCard(1, 1));
	}

	/**
	 *
	 */
	public function testFindCustomerCardFailBt(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$cardrow =
		[
			'id'      => 1,
			'user_id' => 1,
			'token'   => 'foobar',
		];

		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('payment_tokens')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('user_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($cardrow);

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('find')->with('foobar')->andReturn($this->mock('\Braintree\Exception\NotFound'));

		$this->assertFalse($bt->findCustomerCard(1, 1));
	}

	/**
	 *
	 */
	public function testCards(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);
		$cards = [$paymentMethod];

		$btCustomer->paymentMethods = $cards;

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(1)->andReturn($btCustomer);

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('find')->with('foobar')->andReturn($this->mock('\Braintree\Exception\NotFound'));

		$this->assertEquals($bt->cards(), $cards);
	}

	/**
	 *
	 */
	public function testCardsLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);
		$this->assertEquals($bt->cards(), []);
	}

	/**
	 *
	 */
	public function testCardsById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);
		$cards = [$paymentMethod];
		$btCustomer->paymentMethods = $cards;

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(2)->andReturn($btCustomer);

		$this->assertEquals($bt->cards(2), $cards);
	}

	/**
	 *
	 */
	public function testCardsByIdEmpty(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);
		$btCustomer->paymentMethods = [];

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(2)->andReturn($btCustomer);

		$this->assertEquals($bt->cards(2), []);
	}

	/**
	 *
	 */
	public function testCustomer(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(1)->andReturn($btCustomer);

		$this->assertEquals($bt->customer(), $btCustomer);
	}

	/**
	 *
	 */
	public function testCustomerLoggedOut(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals($bt->customer(), false);
	}

	/**
	 *
	 */
	public function testCustomerById(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(1)->andReturn($btCustomer);

		$this->assertEquals($bt->customer(1), $btCustomer);
	}

	/**
	 *
	 */
	public function testCustomerByIdFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('find')->with(2)->andReturn($btCustomer);

		$this->assertEquals($bt->customer(2), $btCustomer);
	}

	/**
	 *
	 */
	public function testCreateCustomer(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$customerRow =
		[
            'firstName' => 'foo',
            'lastName'  => 'bar',
            'email'     => 'foo@bar.com',
            'id'        => 1,
        ];
        $userRow =
        [
        	'id'    => 1,
            'name'  => 'foo bar',
            'email' => 'foo@bar.com',
        ];

        $resultSuccess->customer = $btCustomer;
		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('users')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($userRow);

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('create')->with($customerRow)->andReturn($resultSuccess);

		$this->assertEquals($bt->createCustomer(), $btCustomer);
	}

	/**
	 *
	 */
	public function testCreateCustomerFailLoggedIn(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals($bt->createCustomer(), false);
	}

	/**
	 *
	 */
	public function testCreateCustomerFailBt(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$customerRow =
		[
            'firstName' => 'foo',
            'lastName'  => 'bar',
            'email'     => 'foo@bar.com',
            'id'        => 1,
        ];
        $userRow =
        [
        	'id'    => 1,
            'name'  => 'foo bar',
            'email' => 'foo@bar.com',
        ];

		$sql = $this->sqlBuilderMocks();
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('users')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($userRow);

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('create')->with($customerRow)->andReturn($resultError);

		$this->assertEquals($bt->createCustomer(), false);
	}

	/**
	 *
	 */
	public function testCreateCustomerWithId(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$customerRow =
		[
            'firstName' => 'foo',
            'lastName'  => 'bar',
            'email'     => 'foo@bar.com',
            'id'        => 1,
        ];
        $userRow =
        [
        	'id'    => 1,
            'name'  => 'foo bar',
            'email' => 'foo@bar.com',
        ];

        $resultSuccess->customer = $btCustomer;

		$sql = $this->sqlBuilderMocks();

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('users')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn($userRow);

		$gateway->shouldReceive('customer')->andReturn($customer);
		$customer->shouldReceive('create')->with($customerRow)->andReturn($resultSuccess);

		$this->assertEquals($bt->createCustomer(1), $btCustomer);
	}

	/**
	 *
	 */
	public function testCreateCustomerWithIdFail(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$sql = $this->sqlBuilderMocks();

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('users')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertEquals($bt->createCustomer(1), false);
	}

	/**
	 *
	 */
	public function testAddCard(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$nonceRow =
		[
            'customerId'         => 1,
            'paymentMethodNonce' => 'nonce',
        ];

        $resultSuccess->paymentMethod = $paymentMethod;

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('create')->with($nonceRow)->andReturn($resultSuccess);

		$this->assertEquals($bt->addCard('nonce'), $paymentMethod);
	}

	/**
	 *
	 */
	public function testAddCardFailLogin(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$nonceRow =
		[
            'customerId'         => 1,
            'paymentMethodNonce' => 'nonce',
        ];

        $resultSuccess->paymentMethod = $paymentMethod;

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$this->assertEquals($bt->addCard('nonce'), false);
	}

	/**
	 *
	 */
	public function testAddCardFailBt(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$nonceRow =
		[
            'customerId'         => 1,
            'paymentMethodNonce' => 'nonce',
        ];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('create')->with($nonceRow)->andReturn($resultError);

		$this->assertEquals($bt->addCard('nonce'), false);
	}

	/**
	 *
	 */
	public function testAddCardWithId(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$nonceRow =
		[
            'customerId'         => 2,
            'paymentMethodNonce' => 'nonce',
        ];

        $resultSuccess->paymentMethod = $paymentMethod;

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('create')->with($nonceRow)->andReturn($resultSuccess);

		$this->assertEquals($bt->addCard('nonce', 2), $paymentMethod);
	}

	/**
	 *
	 */
	public function testAddCardFailWithId(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$nonceRow =
		[
            'customerId'         => 2,
            'paymentMethodNonce' => 'nonce',
        ];

        $resultSuccess->paymentMethod = $paymentMethod;

		$gateway->shouldReceive('paymentMethod')->andReturn($paymentMethodGateway);
		$paymentMethodGateway->shouldReceive('create')->with($nonceRow)->andReturn($resultError);

		$this->assertEquals($bt->addCard('nonce', 2), false);
	}

	/**
	 *
	 */
	private function getMocks(): array
	{
		$gateway       = $this->mock('\Braintree\Gateway');
		$bt            = new BrainTree($gateway);
		$config        = $this->mock('\kanso\framework\config\Config');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$btCustomer    = $this->mock('\Braintree\Customer');
		$customer      = $this->mock('\Braintree\CustomerGateway');
		$token         = $this->mock('\Braintree\ClientTokenGateway');
		$transaction   = $this->mock('\Braintree\ClientTokenGateway');
		$resultSuccess = $this->mock('\Braintree\Result\Successful');
		$resultError   = $this->mock('\Braintree\Result\Error');
		$paymentMethod = $this->mock('\Braintree\PaymentMethod');
		$paymentMethodGateway = $this->mock('Braintree\PaymentMethodGateway');

		$user->id            = 1;
		$user->email         = 'foo@bar.com';
		$bt->Gatekeeper = $gatekeeper;
		$bt->Config     = $config;
		$btCustomer->id = 1;

		return
		[
			'bt'            => $bt,
			'config'        => $config,
			'gatekeeper'    => $gatekeeper,
			'user'          => $user,
			'gateway'       => $gateway,
			'cleintToken'   => $token,
			'customer'      => $customer,
			'btCustomer'    => $btCustomer,
			'transaction'   => $transaction,
			'resultSuccess' => $resultSuccess,
			'resultError'   => $resultError,
			'paymentMethod' => $paymentMethod,
			'paymentMethodGateway' => $paymentMethodGateway,
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
