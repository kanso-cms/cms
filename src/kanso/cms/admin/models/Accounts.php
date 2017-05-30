<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\admin\models\Model;

/**
 * Admin panel account pages model
 *
 * @author Joe J. Howard
 */
class Accounts extends Model
{
    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
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
                $this->response->redirect($this->request->environment()->HTTP_HOST.'/admin/articles/');

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
            $login = $this->gatekeeper->login($validated_data['username'], $validated_data['password']);
            
            if ($login === $this->gatekeeper::LOGIN_ACTIVATING)
            {
                return $this->postMessage('warning', 'Your account has not yet been activated.');
            }
            else if ($login === $this->gatekeeper::LOGIN_LOCKED)
            {
                return $this->postMessage('warning', 'That account has been temporarily locked.');
            }
            else if ($login === $this->gatekeeper::LOGIN_BANNED)
            {
                return $this->postMessage('warning', 'hat account has been permanently suspended.');
            }
            else if ($login === true)
            {
                $this->response->redirect($this->request->environment()->HTTP_HOST.'/admin/articles/');
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
            $this->gatekeeper->forgotPassword($validated_data['username']);
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
            $this->gatekeeper->forgotPassword($validated_data['username']);
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
        $token = $this->request->queries('token');

        # If no token was given 404
        if (!$token || trim($token) === '' || $token === 'null' )
        {
            return false;
        }

        # Get the user based on their token
        $user = $this->userManager->provider()->byKey('kanso_password_key', $token, true);

        # Add the token to their session
        if ($user)
        {
            $this->response->session()->set('kanso_password_key', $token);

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
        $token = $this->response->session()->get('kanso_password_key');

        if (!$token)
        {
            return false;
        }

        # Get the user based on their token
        $user = $this->userManager->provider()->byKey('kanso_password_key', $token, true);

        if ($user)
        {
            if ($this->gatekeeper->resetPassword($_POST['password'], $token))
            {
                $this->response->session()->remove('kanso_password_key');

                return $this->postMessage('success', 'Your password was successfully reset.');
            }
        }

        return  $this->postMessage('danger', 'There was an error processing your request.');
    }

    /**
     * Parse a logout GET request
     *
     * @access private
     */
    private function processLogoutGET()
    {
        $this->gatekeeper->logout();

        $this->response->redirect($this->request->environment()->HTTP_HOST);
    }
}
