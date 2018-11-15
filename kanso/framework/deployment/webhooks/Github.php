<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\deployment\webhooks;

use Exception;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\http\response\Response;
use kanso\framework\shell\Shell;
use kanso\framework\utility\Str;

/**
 * Github webhooks implementation.
 *
 * @author Joe J. Howard
 */
class Github implements WebhookInterface
{
    /**
     * Request object.
     *
     * @var \kanso\framework\http\request\Request
     */
    private $request;

    /**
     * Response object.
     *
     * @var \kanso\framework\http\response\Response
     */
    private $response;

    /**
     * Shell utility.
     *
     * @var \kanso\framework\shell\Shell
     */
    private $shell;

    /**
     * Github provided secret.
     *
     * @var string
     */
    private $secret;

    /**
     * Incoming Git event type.
     *
     * @var string
     */
    private $event;

    /**
     * Incoming GitHub payload.
     *
     * @var array
     */
    private $payload;

    /**
     * Incoming file to read.
     *
     * @var string
     */
    public $_fileIn = 'php://input';

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\http\request\Request   $request  Request object
     * @param \kanso\framework\http\response\Response $response Response object
     * @param \kanso\framework\shell\Shell            $shell    Shell utility
     * @param string                                  $secret   Github token
     */
    public function __construct(Request $request, Response $response, Shell $shell, string $secret)
    {
        $this->request = $request;

        $this->response = $response;

        $this->secret = $secret;

        $this->shell = $shell;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        if (!$this->payloadExists())
        {
            throw new RequestException(500, 'Bad POST Request. No payload was provided.');
        }

        if (!$this->headersExist())
        {
            throw new RequestException(500, 'Bad POST Request. Github request headers not provided.');
        }

        if (!$this->validateUserAgent())
        {
            throw new RequestException(500, 'Bad POST Request. Invalid user agent.');
        }

        if (!$this->verifySignature())
        {
            throw new InvalidTokenException('Bad POST Request. Github signature could not be verified.');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function event(): string
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy()
    {
        $this->gitPull();

        $this->composerUpdate();

        $this->response->format()->set('txt');
    }

    /**
     * Update repo via git.
     *
     * @throws \Exception if 'git pull' was unsuccefull
     */
    private function gitPull()
    {
        $this->shell->cd($this->request->environment()->DOCUMENT_ROOT);

        $this->shell->cmd('git', 'pull');

        $response = $this->shell->run();

        if (!$this->shell->is_successful())
        {
            throw new Exception('Error deploying via git. ' . $response);
        }

        $this->response->body()->set("Git: \n" . $response);
    }

    /**
     * Update any comoser dependancies.
     *
     * @throws Exception if 'composer update' was unsuccefull
     */
    private function composerUpdate()
    {
        $this->shell->cd($this->request->environment()->DOCUMENT_ROOT);

        $this->shell->cmd('composer', 'update');

        $response = $this->shell->run();

        if (!$this->shell->is_successful())
        {
            throw new Exception('Error deploying via composer. ' . $response);
        }

        $this->response->body()->append("\n\nComposer: \n" . $response);
    }

    /**
     * Validate a payload exists.
     *
     * @return bool
     */
    private function payloadExists(): bool
    {
        // $_POST
        $post = $this->request->fetch();

        // Validate the payload is set
        if (!isset($post['payload']) || empty(trim($post['payload'])))
        {
            return false;
        }

        return true;
    }

    /**
     * Validate the proper headers exist.
     *
     * @return bool
     */
    private function headersExist(): bool
    {
        $headers = $this->request->headers()->asArray();

        if (!isset($headers['HTTP_X_GITHUB_EVENT']) || !isset($headers['HTTP_X_HUB_SIGNATURE']) || !isset($headers['HTTP_X_GITHUB_DELIVERY']) || !isset($headers['HTTP_USER_AGENT']))
        {
            return false;
        }

        return true;
    }

    /**
     * Validate the user agent is from Github.
     *
     * @return bool
     */
    private function validateUserAgent(): bool
    {
        return Str::contains($this->request->headers()->HTTP_USER_AGENT, 'GitHub-Hookshot/');
    }

    /**
     * Validate the github signature and decode the payload.
     *
     * @access public
     */
    private function verifySignature(): bool
    {
        $token = $this->request->headers()->HTTP_X_HUB_SIGNATURE;

        // Split signature into algorithm and hash
        list($algo, $hash) = explode('=', $token, 2);

        // Get payload
        $payload = file_get_contents($this->_fileIn);

        // Calculate hash based on payload and the secret
        $payloadHash = hash_hmac($algo, $payload, $this->secret);

        // Check if hashes are equivalent
        if ($hash !== $payloadHash)
        {
            return false;
        }

        $this->event = $this->request->headers()->HTTP_X_GITHUB_EVENT;

        $this->payload = json_decode($payload, true);

        return true;
    }
}
