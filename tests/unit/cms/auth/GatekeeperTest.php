<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\auth;

use kanso\cms\auth\Gatekeeper;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class GatekeeperTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructor()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);
	}

	/**
	 *
	 */
	public function testConstructorLoggedIn()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);
	}

	/**
	 *
	 */
	public function testConstructorExpiredCSRF()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);
	}

	/**
	 *
	 */
	public function testIsLoggedInTrue()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertTrue($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testIsLoggedInFalse()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertFalse($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testGetUserTrue()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertEquals($user, $gatekeeper->getUser());
	}

	/**
	 *
	 */
	public function testGetUserFalse()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertNull($gatekeeper->getUser());
	}

	/**
	 *
	 */
	public function testRefreshUser()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$user->id = 1;

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar')->once();

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token']);

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$token->shouldReceive('get')->andReturn('foobar token')->once();

		$cookie->shouldReceive('setMultiple')->with([
            'user_id' => 1,
            'email'   => 'foobar@mail.com',
        ]);

        $session->shouldReceive('setMultiple')->with(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token']);

        $sql->shouldReceive('UPDATE')->with('users')->andReturn($sql);

		$sql->shouldReceive('SET')->with(['access_token' => 'foobar token'])->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('QUERY');

		$cookie->shouldReceive('login');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$gatekeeper->refreshUser();
	}

	/**
	 *
	 */
	public function testGetTokenLoggenIn()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertEquals('foobar', $gatekeeper->token());
	}

	/**
	 *
	 */
	public function testGetTokenLoggenOut()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertEquals('foobar', $gatekeeper->token());
	}

	/**
	 *
	 */
	public function testIsGuestTrue()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertTrue($gatekeeper->isGuest());
	}

	/**
	 *
	 */
	public function testIsGuestFalse()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$user->role = 'administrator';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertFalse($gatekeeper->isGuest());

		$user->role = 'writer';

		$this->assertFalse($gatekeeper->isGuest());

		$user->role = 'customer';

		$this->assertTrue($gatekeeper->isGuest());
	}

	/**
	 *
	 */
	public function testIsAdmin()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$user->role = 'administrator';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertTrue($gatekeeper->isAdmin());

		$user->role = 'writer';

		$this->assertTrue($gatekeeper->isAdmin());

		$user->role = 'customer';

		$this->assertFalse($gatekeeper->isAdmin());
	}

	/**
	 *
	 */
	public function testverifyTokenLoggedIn()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$this->assertTrue($gatekeeper->verifyToken('foobar'));

	}

	/**
	 *
	 */
	public function testverifyTokenLoggedOut()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$this->assertTrue($gatekeeper->verifyToken('foobar'));

		$this->assertFalse($gatekeeper->verifyToken('foobaz'));
	}

	/**
	 *
	 */
	public function testLogin()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('verify')->with('raw password', 'hased password')->andReturn(true);

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar token')->once();

		$cookie->shouldReceive('setMultiple')->with([
            'user_id' => 1,
            'email'   => 'foobar@mail.com',
        ]);

        $session->shouldReceive('setMultiple')->with(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

        $sql->shouldReceive('UPDATE')->with('users')->andReturn($sql);

		$sql->shouldReceive('SET')->with(['access_token' => 'foobar token'])->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('QUERY');

		$cookie->shouldReceive('login');

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$this->assertTrue($gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginIncorrectPass()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('verify')->with('raw password', 'hased password')->andReturn(false);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginPending()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'pending', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_ACTIVATING, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginBanned()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'banned', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_BANNED, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginLocked()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'locked', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_LOCKED, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginDoesntExist()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn([]);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginByUsername()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('username', '=', 'foobar')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn([]);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foobar', 'raw password'));
	}

	/**
	 *
	 */
	public function testLogout()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true)->once();

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$cookie->shouldReceive('logout');

		$gatekeeper->logout();

		$cookie->shouldReceive('isLoggedIn')->andReturn(false)->once();

		$this->assertFalse($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testForgotPassowrd()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$userProvider->shouldReceive('byKey')->with('email', 'foo@bar.com', true)->andReturn($user);

		$user->shouldReceive('save')->once();

		$this->assertTrue($gatekeeper->forgotPassword('foo@bar.com', false));

		$this->assertTrue($user->kanso_password_key !== null);
	}

	/**
	 *
	 */
	public function testForgotPassowrdByUsername()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$userProvider->shouldReceive('byKey')->with('username', 'usernamefoo', true)->andReturn($user);

		$user->shouldReceive('save')->once();

		$this->assertTrue($gatekeeper->forgotPassword('usernamefoo', false));

		$this->assertTrue($user->kanso_password_key !== null);
	}

	/**
	 *
	 */
	public function testForgotPassowrdWithEmail()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$userProvider->shouldReceive('byKey')->with('username', 'usernamefoo', true)->andReturn($user);

		$user->shouldReceive('save')->once();

		$email->shouldReceive('forgotPassword')->with($user);

		$this->assertTrue($gatekeeper->forgotPassword('usernamefoo', true));

		$this->assertTrue($user->kanso_password_key !== null);
	}

	/**
	 *
	 */
	public function testResetPassword()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$userProvider->shouldReceive('byKey')->with('kanso_password_key', 'foobartoken', true)->andReturn($user);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('hash')->with('password')->andReturn('encrypted password');

		$user->shouldReceive('save')->once();

		$email->shouldReceive('resetPassword')->with($user);

		$this->assertTrue($gatekeeper->resetPassword('password', 'foobartoken'));
	}

	/**
	 *
	 */
	public function testForgotUsername()
	{
		$sql          = Mockery::mock('\kanso\framework\database\query\Builder');
		$userProvider = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');
		$crypto       = Mockery::mock('\kanso\framework\security\Crypto');
		$cookie       = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session      = Mockery::mock('\kanso\framework\http\session\Session');
		$email        = Mockery::mock('\kanso\cms\auth\adapters\EmailAdapter');
		$token        = Mockery::mock('\kanso\framework\http\session\Token');
		$user         = Mockery::mock('\kanso\cms\wrappers\User');
		$password     = Mockery::mock('\kanso\framework\security\password\encrypters\NativePHP');

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session, $email);

		$userProvider->shouldReceive('byKey')->with('email', 'foo@bar.com', true)->andReturn($user);

		$email->shouldReceive('forgotUsername')->with($user);

		$this->assertTrue($gatekeeper->forgotUsername('foo@bar.com'));
	}
}
