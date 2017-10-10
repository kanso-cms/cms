<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use Exception;
use kanso\cms\admin\models\BaseModel;

/**
 * Admin panel account pages model
 *
 * @author Joe J. Howard
 */
class Accounts extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new Exception('Bad Admin Panel POST Request. The CSRF token was either not provided or invalid.');
        }
        
        if ($this->requestName === 'login')
        {
            if (!$this->isLoggedIn)
            {
                return $this->processLoginPOST();
            }
        }
        else if ($this->requestName === 'forgotpassword')
        {
            if (!$this->isLoggedIn)
            {
                return $this->processForgotPassowordPOST();
            }
        }
        else if ($this->requestName === 'forgotusername')
        {
            if (!$this->isLoggedIn)
            {
                return $this->processForgotUsernamePOST();
            }
        }
        else if ($this->requestName === 'resetpassword')
        {
            if (!$this->isLoggedIn)
            {
                return $this->processResetpasswordPOST();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->requestName === 'login' || $this->requestName === 'forgotusername' || $this->requestName === 'forgotpassword')
        {
            if ($this->isLoggedIn)
            {
                $this->Response->redirect($this->Request->environment()->HTTP_HOST.'/admin/posts/');

                return false;
            }

            return [];
        }
        else if ($this->requestName === 'resetpassword')
        {
            if (!$this->isLoggedIn)
            {
                if ($this->validateResetPasswordGET())
                {
                    return [];
                }
            }
        }
        else if ($this->requestName === 'logout')
        {
            if ($this->isLoggedIn)
            {
                return $this->processLogoutGET();
            }
        }

        return false;
    }

    /**
     * Parse a login request via POST
     *
     * @access private
     * @return array
     */
    private function processLoginPOST(): array
    {
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
            'password'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
            'password' => 'trim',
        ]);

        $validated_data = $this->validation->run($post);

        if ($validated_data)
        {
            $user  = $this->UserManager->byUsername($validated_data['username']);
            
            if (!$user || $user->role !== 'administrator')
            {
                return $this->postMessage('danger', 'Either the username or password you entered was incorrect.');
            }

            $login = $this->Gatekeeper->login($validated_data['username'], $validated_data['password']);
            
            if ($login === $this->Gatekeeper::LOGIN_ACTIVATING)
            {
                return $this->postMessage('warning', 'Your account has not yet been activated.');
            }
            else if ($login === $this->Gatekeeper::LOGIN_LOCKED)
            {
                return $this->postMessage('warning', 'That account has been temporarily locked.');
            }
            else if ($login === $this->Gatekeeper::LOGIN_BANNED)
            {
                return $this->postMessage('warning', 'That account has been permanently suspended.');
            }
            else if ($login === true)
            {
                $this->Response->redirect($this->Request->environment()->HTTP_HOST.'/admin/posts/');
            }
        }

        return $this->postMessage('danger', 'Either the username or password you entered was incorrect.');
    }

    /**
     * Parse a forgot password request via POST
     *
     * @access private
     * @return array
     */
    private function processForgotPassowordPOST(): array
    {
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        if ($validated_data)
        {
            $user = $this->UserManager->byUsername($validated_data['username']);
            
            if ($user || $user->role === 'administrator')
            {
                $this->Gatekeeper->forgotPassword($validated_data['username']);
            }
        }

        return $this->postMessage('success', 'If a user is registered under that username, they were sent an email to reset their password.');
    }

    /**
     * Parse a forgot password request via POST
     *
     * @access private
     * @return array
     */
    private function processForgotUsernamePOST(): array
    {
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        if ($validated_data)
        {
            $user = $this->UserManager->byUsername($validated_data['username']);
            
            if ($user || $user->role === 'administrator')
            {
                $this->Gatekeeper->forgotPassword($validated_data['username']);
            }
        }

        return $this->postMessage('success', 'If a user is registered under that email address, they were sent an email with their username.');
    }

    /**
     * Validate a GET request to reset password page
     *
     * @access private
     * @return bool
     */
    private function validateResetPasswordGET(): bool
    {
        # Get the token in the url
        $token = $this->Request->queries('token');

        # If no token was given 404
        if (!$token || trim($token) === '' || $token === 'null' )
        {
            return false;
        }

        # Get the user based on their token
        $user = $this->UserManager->provider()->byKey('kanso_password_key', $token, true);

        # Add the token to their session
        if ($user)
        {
            $this->Response->session()->set('kanso_password_key', $token);

            return true;
        }

        return false;
    }

    /**
     * Parse a reset password POST request
     *
     * @access private
     * @return array||false
     */
    private function processResetpasswordPOST()
    {
        # $_POST password must be set - get directly from POST so it is untouched
        if (!isset($_POST['password']))
        {
            return false;
        }

        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'username'  => 'required|max_len,100|min_len,5',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($post);

        # Make sure the user's token is in the session and they match
        $token = $this->Response->session()->get('kanso_password_key');

        if (!$token)
        {
            return false;
        }

        # Get the user based on their token
        $user = $this->UserManager->provider()->byKey('kanso_password_key', $token, true);

        if ($user)
        {
            if ($this->Gatekeeper->resetPassword($_POST['password'], $token))
            {
                $this->Response->session()->remove('kanso_password_key');

                return $this->postMessage('success', 'Your password was successfully reset.');
            }
        }

        return $this->postMessage('danger', 'There was an error processing your request.');
    }

    /**
     * Parse a logout GET request
     *
     * @access private
     */
    private function processLogoutGET()
    {
        $this->Gatekeeper->logout();

        $this->Response->redirect($this->Request->environment()->HTTP_HOST);
    }
    
}
