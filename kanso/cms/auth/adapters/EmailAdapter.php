<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\auth\adapters;

use kanso\cms\email\Email;
use kanso\cms\wrappers\User;

/**
 * CMS gatekeeper
 *
 * @author Joe J. Howard
 */
class EmailAdapter
{
    /**
     * httpHost
     * 
     * @var string
     */
    private $httpHost;

    /**
     * domainName
     * 
     * @var string
     */
    private $domainName;

    /**
     * Name of website
     * 
     * @var string
     */
    private $siteTitle;
    
    /**
     * Default urls
     * 
     * @var array
     */
    private $urls =
    [
        'login'           => 'login/',
        'register'        => 'register/',
        'forgot_password' => 'forgot-password/',
        'reset_password'  => 'reset-password/',
        'confirm_account' => 'confirm-account/'
    ];

    public function __construct(Email $email, string $httpHost, string $domainName, string $siteTitle, array $urls = [])
    {
        $this->email = $email;

        $this->httpHost = $httpHost;

        $this->domainName = $domainName;

        $this->siteTitle = $siteTitle;

        $this->urls = $urls ?? $this->urls;
    }

    public function forgotPassword(User $user)
    {
        $resetUrl = $this->httpHost.'/'.$this->urls['reset_password'].'?token='.$user->kanso_password_key;
            
        if ($user->role === 'administrator' || $user->role === 'writer')
        {
            $resetUrl = $this->httpHost.'/admin/reset-password/?token='.$user->kanso_password_key;
        }

        $emailData = [
            'name'        => $user->name, 
            'resetUrl'    => $resetUrl,
            'websiteName' => $this->domainName,
            'websiteUrl'  => $this->httpHost,
        ];

        # Email credentials
        $senderName   = $this->siteTitle;
        $senderEmail  = 'no-reply@'.$this->domainName;
        $emailSubject = 'Request to reset your password';
        $emailContent = $this->email->html($emailSubject, $this->email->preset('forgot-password', $emailData));
        $emailTo      = $user->email;

        return $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
    }
    

    /**
     * Forgot password
     *
     * @access public
     * @param  string $username  Username or email address for user to reset password
     * @param  bool   $sendEamil Send the user an email (optional) (default true)
     * @return bool
     */
    public function forgotPassword(string $username, bool $sendEamil = true): bool
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL))
        {
            $user = $this->provider->byKey('email', $username, true);
        }
        else
        {
            $user = $this->provider->byKey('username', $username, true);
        }

        if (!$user)
        {
            return false;
        }

        # Create a token for them
        $user->kanso_password_key = UUID::v4();
        
        $user->save();

        if ($sendEamil)
        {
            $resetUrl = $this->httpHost.'/'.$this->config->get('email.urls.reset_password').'?token='.$user->kanso_password_key;
            
            if ($user->role === 'administrator' || $user->role === 'writer')
            {
                $resetUrl = $this->httpHost.'/admin/reset-password/?token='.$user->kanso_password_key;
            }

            $emailData = [
                'name'        => $user->name, 
                'resetUrl'    => $resetUrl,
                'websiteName' => $this->domainName,
                'websiteUrl'  => $this->httpHost,
            ];

            # Email credentials
            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@'.$this->domainName;
            $emailSubject = 'Request to reset your password';
            $emailContent = $this->email->html($emailSubject, $this->email->preset('forgot-password', $emailData));
            $emailTo      = $user->email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
        }

        return true;
    }

    /**
     * Reset password
     *
     * @access public
     * @param  string $password  New password
     * @param  string $token     Reset token from the database
     * @param  bool   $sendEamil Reset token from the database
     * @return bool
     */
    public function resetPassword(string $password, string $token, bool $sendEamil = true): bool
    {
        # Validate the user exists
        $user = $this->provider->byKey('kanso_password_key', $token, true);
        
        if (!$user)
        {
            return false;
        }

        $user->kanso_password_key = '';
        $user->hashed_pass = utf8_encode($this->crypto->password()->hash($password));
        $user->save();

        if ($sendEamil)
        {
            $emailData =
            [
                'name'        => $user->name, 
                'websiteName' => $this->domainName,
                'websiteUrl'  => $this->httpHost,
                'resetUrl'    => $this->httpHost.'/'.$this->config->get('email.urls.forgot_password'),
                'loginUrl'    => $this->httpHost.'/'.$this->config->get('email.urls.login'),
            ];

            if ($user->role === 'administrator' || $user->role === 'writer')
            {
                $emailData['resetUrl']  = $this->httpHost.'/admin/forgot-password/';
                $emailData['loginUrl']  = $this->httpHost.'/admin/login/';
            }

            # Email credentials
            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@'.$this->domainName;
            $emailSubject = 'Your password was reset at '.$this->domainName;
            $emailContent = $this->email->html($emailSubject, $this->email->preset('reset-password', $emailData));
            $emailTo      = $user->email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
        }

        return true;
    }


    /**
     * Forgot username
     *
     * @access public
     * @param  string $email Email for user reminder to be sent
     * @return bool
     */
    public function forgotUsername(string $email): bool
    {
        # Validate the user exists
        $user = $this->provider->byKey('email', $email, true);

        if (!$user)
        {
            return false;
        }

        # email variables
        $emailData = [
            'name'        => $user->name, 
            'username'    => $user->username,
            'websiteName' => $this->domainName,
            'websiteUrl'  => $this->httpHost,
        ];

        # Email credentials
        $senderName   = $this->config->get('cms.site_title');
        $senderEmail  = 'no-reply@'.$this->domainName;
        $emailSubject = 'Username reminder at '.$this->domainName;
        $emailContent = $this->email->html($emailSubject, $this->email->preset('forgot-username', $emailData));
        $emailTo      = $user->email;

        $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

        return true;
    }

    /**
     * Log client in
     *
     * @access private
     * @param  array $_user Row from database  
     */
    private function logClientIn(array $_user) 
    {        
        # Create a fresh cookie
        $this->cookie->destroy();

        $this->session->destroy();

        # Get the new access token
        $token = $this->session->token()->get();
        $_user['access_token'] = $token;

        # Add the user credentials
        $this->cookie->setMultiple([
            'user_id' => $_user['id'],
            'email'   => $_user['email'],
        ]);

        # Save everything to session
        $this->session->setMultiple($_user);

        # Update the user's access token in the DB
        # to match the newly created one
        $this->SQL
            ->UPDATE('users')->SET(['access_token' => $token])
            ->WHERE('id', '=', $_user['id'])
            ->QUERY();

        # Log the client in
        $this->cookie->login();

        # Save the user
        $this->user = $this->provider->byId($_user['id']);
    }
}
