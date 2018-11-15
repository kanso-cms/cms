<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session;

/**
 * Session CSRF Token.
 *
 * @author Joe J. Howard
 */
class Token
{
    /**
     * The token.
     *
     * @var string
     */
    private $token = '';

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Regenerate the token.
     *
     * @access public
     * @return string
     */
    public function get(): string
    {
        return $this->token;
    }

    /**
     * Set the token.
     *
     * @access public
     * @return string
     */
    public function set(string $token)
    {
        return $this->token = $token;
    }

    /**
     * Regenerate the token.
     *
     * @access public
     * @return string
     */
    public function regenerate(): string
    {
        $this->token = hash('sha256', random_bytes(16));

        return $this->token;
    }

    /**
     * Verify a user's access token.
     *
     * @access public
     * @param  string $token A token to make the comparison with
     * @return bool
     */
    public function verify(string $token): bool
    {
        return $token === $this->token;
    }
}
