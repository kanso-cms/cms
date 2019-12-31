<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\crm;

use kanso\cms\crm\Crm;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CrmTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructorNewVisitor(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$leadArgs =
		[
			'ip_address'  => '192.168.1.1',
			'name'        => '',
			'email'       => '',
			'last_active' => time(),
			'user_agent'  => 'Safari 11.2',
			'is_bot'      => false,
		];

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('has')->with('crm_visitor_id')->andReturn(false);
		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);

		$leadProvider->shouldReceive('create')->with($leadArgs)->andReturn($visitor);
		$cookie->shouldReceive('put');

		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);
	}

	/**
	 *
	 */
	public function testConstructorLoggedIn(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);
	}

	/**
	 *
	 */
	public function testConstructorReturning(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('has')->with('crm_visitor_id')->andReturn(true);
		$cookie->shouldReceive('get')->with('crm_visitor_id')->andReturn('visitor_lead_id');
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);
	}

	/**
	 *
	 */
	public function testConstructorReturningNoCookie(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$leadArgs =
		[
			'ip_address'  => '192.168.1.1',
			'name'        => '',
			'email'       => '',
			'last_active' => time(),
			'user_agent'  => 'Safari 11.2',
			'is_bot'      => false,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('has')->with('crm_visitor_id')->andReturn(true);
		$cookie->shouldReceive('get')->with('crm_visitor_id')->andReturn('visitor_lead_id');
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn(false);
		$leadProvider->shouldReceive('create')->with($leadArgs)->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);
	}

	/**
	 *
	 */
	public function testConstructorLoggedInNoCookie(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$leadArgs =
		[
			'ip_address'  => '192.168.1.1',
			'name'        => '',
			'email'       => '',
			'last_active' => time(),
			'user_agent'  => 'Safari 11.2',
			'is_bot'      => false,
		];

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn(false);
		$leadProvider->shouldReceive('create')->with($leadArgs)->andReturn($visitor);
		$user->shouldReceive('save');
		$visitor->shouldReceive('save');

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);
	}

	/**
	 *
	 */
	public function testVisitor(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('has')->with('crm_visitor_id')->andReturn(true);
		$cookie->shouldReceive('get')->with('crm_visitor_id')->andReturn('visitor_lead_id');
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$this->assertTrue($crm->visitor() === $visitor);
	}

	/**
	 *
	 */
	public function testLeadProvider(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('has')->with('crm_visitor_id')->andReturn(true);
		$cookie->shouldReceive('get')->with('crm_visitor_id')->andReturn('visitor_lead_id');
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$this->assertTrue($crm->leadProvider() === $leadProvider);
	}

	/**
	 *
	 */
	public function testLogin(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$visitor->shouldReceive('save');
		$user->shouldReceive('save');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$crm->login();

		$this->assertTrue($visitor->email === $user->email);
	}

	/**
	 *
	 */
	public function testLogout(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$visitor->shouldReceive('save');
		$user->shouldReceive('save');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$crm->logout();
 	}

	/**
	 *
	 */
	public function testMerge(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$visitor->shouldReceive('save');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('crm_visitors')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('visitor_id', '=', 'new_visitor_id')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn(['visitor_id' => 'new_visitor_id', 'name' => 'changed']);

		$cookie->shouldReceive('put')->with('crm_visitor_id', 'new_visitor_id');

		$crm->mergeVisitor('new_visitor_id');

		$this->assertTrue($visitor->visitor_id === 'new_visitor_id');
 	}

	/**
	 *
	 */
	public function testMergeReturning(): void
	{
		$request       = $this->mock('\kanso\framework\http\request\Request');
		$response      = $this->mock('\kanso\framework\http\response\Response');
		$cookie        = $this->mock('\kanso\framework\http\cookie\Cookie');
		$gatekeeper    = $this->mock('\kanso\cms\auth\Gatekeeper');
		$user          = $this->mock('\kanso\cms\wrappers\User');
		$leadProvider  = $this->mock('\kanso\cms\wrappers\providers\LeadProvider');
		$sql           = $this->mock('\kanso\framework\database\query\Builder');
		$env           = $this->mock('\kanso\framework\http\request\Environment');
		$visitor       = $this->mock('\kanso\cms\wrappers\Visitor');
		$visitor->visitor_id  = 'visitor_lead_id';
		$visitor->id          = 2;
		$user->id             = 1;
		$user->email          = 'foo@bar.com';
		$user->visitor_id     = 'visitor_lead_id';
		$env->HTTP_USER_AGENT = 'Safari 11.2';
		$env->REMOTE_ADDR     = '192.168.1.1';

		$gatekeeper->shouldReceive('isLoggedIn')->andReturn(true);
		$gatekeeper->shouldReceive('getUser')->andReturn($user);
		$leadProvider->shouldReceive('byKey')->with('visitor_id', 'visitor_lead_id')->andReturn($visitor);

		$response->shouldReceive('cookie')->andReturn($cookie);
		$cookie->shouldReceive('put')->with('crm_visitor_id', 'visitor_lead_id');

		$request->shouldReceive('isGet')->andReturn(true);
		$request->shouldReceive('queries')->andReturn([]);
		$request->shouldReceive('environment')->andReturn($env);
		$visitor->shouldReceive('addVisit');

		$visitor->shouldReceive('save');

		$crm = new Crm($request, $response, $gatekeeper, $leadProvider, $sql, false, false, false);

		$sql->shouldReceive('SELECT')->with('*')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('crm_visitors')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('visitor_id', '=', 'new_visitor_id')->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn(['visitor_id' => 'new_visitor_id', 'name' => 'changed']);

		$sql->shouldReceive('DELETE_FROM')->with('crm_visitors')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('id', '=', 2)->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);

		$sql->shouldReceive('UPDATE')->with('crm_visits')->times(1)->andReturn($sql);
		$sql->shouldReceive('SET')->with(['visitor_id' => 'new_visitor_id'])->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('visitor_id', '=', 'visitor_lead_id')->times(1)->andReturn($sql);
		$sql->shouldReceive('QUERY')->times(1)->andReturn(1);

		$cookie->shouldReceive('put')->with('crm_visitor_id', 'new_visitor_id');

		$crm->mergeVisitor('new_visitor_id');
		$this->assertTrue($visitor->visitor_id === 'new_visitor_id');
 	}
}
