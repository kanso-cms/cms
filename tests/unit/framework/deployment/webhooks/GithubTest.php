<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\deployment\Github;

use Exception;
use kanso\framework\deployment\webhooks\Github;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class GithubTest extends TestCase
{
    /**
     *
     */
    private $validPayload = '{"action":"opened","issue":{"url":"https://api.github.com/repos/octocat/Hello-World/issues/1347","number":1347},"repository":{"id":1296269,"full_name":"octocat/Hello-World","owner":{"login":"octocat","id":1}},"sender":{"login":"octocat","id":1}}';

    /**
     *
     */
    private $invalidPayload = '{action":"opened","issue":{"url":"https://api.github.com/repos/octocat/Hello-World/issues/1347","number":1347},"repository":{"id":1296269,"full_name":"octocat/Hello-World","owner":{"login":"octocat","id":1}},"sender":{"login":"octocat","id":1}}';

    /**
     *
     */
    private $secret = 'my_secret';

    /**
     *
     */
    private $inputFile = 'input.php';

    /**
     *
     */
    public function testValidate(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $github->validate();
    }

    /**
     *
     */
    public function testNoPayload(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn([]);

        $this->expectException(RequestException::class);

        $github->validate();
    }

    /**
     *
     */
    public function testWrongHeaders(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, 'invalid_secret');
        $requestHeaders = $this->validHeaders();

        unset($requestHeaders['HTTP_X_HUB_SIGNATURE']);

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $this->expectException(RequestException::class);

        $github->validate();
    }

    /**
     *
     */
    public function testWrongUserAgent(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        $requestHeaders['HTTP_USER_AGENT'] = 'foobar';

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $this->expectException(RequestException::class);

        $github->validate();
    }

    /**
     *
     */
    public function testInvalidSignature(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        $requestHeaders['HTTP_X_HUB_SIGNATURE'] = 'sha1=342342-4324-423424-423j';

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $this->expectException(InvalidTokenException::class);

        $github->validate();
    }

    /**
     *
     */
    public function testInvalidPayload(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        file_put_contents(dirname(__FILE__) . '/' . $this->inputFile, $this->invalidPayload);

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $this->expectException(InvalidTokenException::class);

        $github->validate();
    }

    /**
     *
     */
    public function testEvent(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        file_put_contents(dirname(__FILE__) . '/' . $this->inputFile, $this->validPayload);

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $github->validate();

        $this->assertEquals('issues', $github->event());
    }

    /**
     *
     */
    public function testPayload(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        file_put_contents(dirname(__FILE__) . '/' . $this->inputFile, $this->validPayload);

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $github->validate();

        $this->assertEquals(json_decode($this->validPayload, true), $github->payload());
    }

    /**
     *
     */
    public function testDeploySuccessful(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $env            = $this->mock('\kanso\framework\http\request\Environment');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $body           = $this->mock('\kanso\framework\http\response\Body');
        $format         = $this->mock('\kanso\framework\http\response\Format');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $env->DOCUMENT_ROOT = '/foo/bar/public_html';

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $request->shouldReceive('environment')->andReturn($env);

        $shell->shouldReceive('cd')->withArgs(['/foo/bar/public_html'])->once();

        $shell->shouldReceive('cmd')->withArgs(['git', 'pull'])->once();

        $response->shouldReceive('body')->andReturn($body)->once();

        $body->shouldReceive('set')->withArgs(["Git: \nshell response"]);

        $shell->shouldReceive('run')->andReturn('shell response')->once();

        $shell->shouldReceive('is_successful')->andReturn(true)->once();

        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('set')->withArgs(['txt']);

        $github->validate();

        $github->deploy();
    }

    /**
     *
     */
    public function testDeployFailGit(): void
    {
        $request        = $this->mock('\kanso\framework\http\request\Request');
        $env            = $this->mock('\kanso\framework\http\request\Environment');
        $headers        = $this->mock('\kanso\framework\http\request\Headers');
        $response       = $this->mock('\kanso\framework\http\response\Response');
        $body           = $this->mock('\kanso\framework\http\response\Body');
        $format         = $this->mock('\kanso\framework\http\response\Format');
        $shell          = $this->mock('\kanso\framework\shell\Shell');
        $github         = new Github($request, $response, $shell, $this->secret);
        $requestHeaders = $this->validHeaders();

        foreach ($requestHeaders as $key => $value)
        {
            $headers->$key = $value;
        }

        $env->DOCUMENT_ROOT = '/foo/bar/public_html';

        $github->_fileIn = dirname(__FILE__) . '/' . $this->inputFile;

        $request->shouldReceive('fetch')->andReturn(['payload' => $this->validPayload]);

        $request->shouldReceive('headers')->andReturn($headers);

        $headers->shouldReceive('asArray')->andReturn($requestHeaders);

        $request->shouldReceive('environment')->andReturn($env);

        $shell->shouldReceive('cd')->withArgs(['/foo/bar/public_html'])->once();

        $shell->shouldReceive('cmd')->withArgs(['git', 'pull'])->once();

        $shell->shouldReceive('run')->andReturn('git failed')->once();

        $shell->shouldReceive('is_successful')->andReturn(false)->once();

        $this->expectException(Exception::class);

        $github->validate();

        $github->deploy();
    }

    /**
     *
     */
    private function validHeaders()
    {
        return
        [
            'POST'                   => '/payload HTTP/1.1',
            'HTTP_HOST'              => 'localhost:8888',
            'HTTP_USER_AGENT'        => 'GitHub-Hookshot/044aadd',
            'Content_Type'           => 'application/json',
            'Content_Length'         =>  6615,
            'HTTP_X_GITHUB_DELIVERY' => '72d3162e-cc78-11e3-81ab-4c9367dc0958',
            'HTTP_X_HUB_SIGNATURE'   => 'sha1=' . hash_hmac('sha1', $this->validPayload, 'my_secret'),
            'HTTP_X_GITHUB_EVENT'    => 'issues',
        ];
    }
}
