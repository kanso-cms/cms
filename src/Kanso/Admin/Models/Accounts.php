<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for account pages
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel account pages
 * login, register, forgot password etc...
 *
 * The class is instantiated by the respective controller
 */
class Accounts
{
    /**
     * @var \Kanso\Utility\GUMP
     */
    private $validation;

    /**
     * @var array $_POST;
     */
    private $postVars;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->validation  = \Kanso\Kanso::getInstance()->Validation;
        $this->postVars    = \Kanso\Kanso::getInstance()->Request->fetch();
    }

    /**
     * Parse a login request via POST
     *
     * @return boolean
     */
    public function login()
    {
        $postVars = $this->validation->sanitize($this->postVars);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
            'password'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
            'password' => 'trim',
        ]);

        $validated_data = $this->validation->run($postVars);

        if ($validated_data) return \Kanso\Kanso::getInstance()->Gatekeeper->login($validated_data['username'], $validated_data['password']);

        return false;
    }

    /**
     * Parse a forgot password request via POST
     *
     * @return boolean
     */
    public function forgotpassword()
    {
        $postVars = $this->validation->sanitize($this->postVars);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($postVars);

        if ($validated_data) return \Kanso\Kanso::getInstance()->Gatekeeper->forgotPassword($validated_data['username']);

        return false;
    }

    /**
     * Parse a forgot username request via POST
     *
     * @return boolean
     */
    public function forgotusername()
    {
        $postVars = $this->validation->sanitize($this->postVars);

        $this->validation->validation_rules([
            'email'  => 'required|valid_email',
        ]);

        $this->validation->filter_rules([
            'email' => 'trim|sanitize_email',
        ]);

        $validated_data = $this->validation->run($postVars);

        if ($validated_data) return \Kanso\Kanso::getInstance()->Gatekeeper->forgotUsername($validated_data['email']);

        return false;
    }

    /**
     * Parse a forgot register request via POST
     *
     * @return boolean
     */
    public function register($token)
    {
        # Get the key from the user's session
        $sessionKey = \Kanso\Kanso::getInstance()->Session->get('session_kanso_register_key');

        # Validate the token and session key are the same
        if ($sessionKey !== $token) return false;

        # Sanitize and validate the POST variables
        $postVars = $this->validation->sanitize($this->postVars);

        $this->validation->validation_rules([
            'username'    => 'required|max_len,100|min_len,5',
            'password'    => 'required|max_len,100|min_len,5',
        ]);
        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
            'password' => 'trim',
        ]);
        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        # Activate the user
        $activate = \Kanso\Kanso::getInstance()->Gatekeeper->activateUser($validated_data['username'], $validated_data['password'], $token);

        if ($activate) {
            \Kanso\Kanso::getInstance()->Gatekeeper->logClientIn($activate);
            return true;
        }

        return false;
    }

}