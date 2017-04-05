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

        $gatekeeper = \Kanso\Kanso::getInstance()->Gatekeeper;

        if ($validated_data) {
            $login = $gatekeeper->login($validated_data['username'], $validated_data['password']);
            if ($login === $gatekeeper::LOGIN_ACTIVATING) {
                return ['class' => 'warning', 'icon' => 'exclamation-triangle', 'msg' => 'Your account has not yet been activated.'];
            }
            else if ($login === $gatekeeper::LOGIN_LOCKED) {
                return ['class' => 'warning', 'icon' => 'exclamation-triangle', 'msg' => 'That account has been temporarily locked due to too many failed login attempts.'];
            }
            else if ($login === $gatekeeper::LOGIN_BANNED) {
                return ['class' => 'warning', 'icon' => 'exclamation-triangle', 'msg' => 'That account has been permanently suspended.'];
            }
            else if ($login === true) {
                return true;
            }
        }
        return ['class' => 'danger', 'icon' => 'times', 'msg' => 'Either the username or password you entered was incorrect.'];
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
     * Parse a reset password GET request
     *
     * @return boolean
     */
    public function resetpasswordGET()
    {
        # Get the token in the url
        $token = \Kanso\Kanso::getInstance()->Request->queries('token');

        # If no token was given 404
        if (!$token || trim($token) === '' || $token === 'null' ) return \Kanso\Kanso::getInstance()->notFound();

        # Get the user based on their token
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUserProvider()->byKey('kanso_password_key', $token, true);

        # Validate the token exists
        if (empty($user)) return false;

        # Add the token to client's session
        \Kanso\Kanso::getInstance()->Session->put('kanso_password_key', $token);

        return true;
    }

    /**
     * Parse a reset password POST request
     *
     * @return boolean
     */
    public function resetpasswordPOST()
    {
        # Get the token from the referrer
        $_token = \Kanso\Kanso::getInstance()->Session->getReferrer();
        if (!$_token) return false;
        $_token = explode('token=', $_token);
        if (!isset($_token[1])) return false;
        $token = $_token[1];

        # If no token was given 404
        if (!$token || trim($token) === '' || $token === 'null' ) return false;

        # Make sure the user's token is in the session and they match
        $sesssionToken =  \Kanso\Kanso::getInstance()->Session->get('kanso_password_key');
        if (!$sesssionToken || $sesssionToken !== $token) return false;

        # Get the user based on their token
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUserProvider()->byKey('kanso_password_key', $token, true);

        # Validate the token exists
        if (empty($user)) return false;

        # $_POST password must be set - get directly from POST so it is untouched
        if (!isset($_POST['password'])) return false;

        # Remove the session key
        \Kanso\Kanso::getInstance()->Session->remove('kanso_password_key');

        # Reset the user's password
        $reset = \Kanso\Kanso::getInstance()->Gatekeeper->resetPassword($_POST['password'], $token);

        if ($reset) {
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your password was successfully reset.'];
        }
        
        return ['class' => 'danger', 'icon' => 'times', 'msg' => 'There was an error processing your request.'];

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