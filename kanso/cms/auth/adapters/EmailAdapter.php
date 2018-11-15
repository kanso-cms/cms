<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\auth\adapters;

use kanso\cms\email\Email;
use kanso\cms\wrappers\User;

/**
 * CMS gatekeeper email adapter.
 *
 * @author Joe J. Howard
 */
class EmailAdapter
{
    /**
     * Email utility.
     *
     * @var \kanso\cms\email\Email
     */
    private $email;

    /**
     * httpHost.
     *
     * @var string
     */
    private $httpHost;

    /**
     * domainName.
     *
     * @var string
     */
    private $domainName;

    /**
     * Name of website.
     *
     * @var string
     */
    private $siteTitle;

    /**
     * Default urls.
     *
     * @var array
     */
    private $urls =
    [
        'login'           => 'login/',
        'register'        => 'register/',
        'forgot_password' => 'forgot-password/',
        'reset_password'  => 'reset-password/',
        'confirm_account' => 'confirm-account/',
    ];

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\cms\email\Email $email      Email utility
     * @param string                 $httpHost   HTTP_HOST
     * @param string                 $domainName Current domain name (e.g 'example.com')
     * @param string                 $siteTitle  Website base title (e.g 'Kanso CMS')
     * @param array                  $urls       Assoc array of CMS urls for user access (otional defaul [])
     */
    public function __construct(Email $email, string $httpHost, string $domainName, string $siteTitle, array $urls = [])
    {
        $this->email = $email;

        $this->httpHost = $httpHost;

        $this->domainName = $domainName;

        $this->siteTitle = $siteTitle;

        $this->urls = $urls ?? $this->urls;
    }

    /**
     * Forgot password.
     *
     * @access public
     * @param  \kanso\cms\wrappers\User $user User to run request on
     * @return bool
     */
    public function forgotPassword(User $user): bool
    {
        $resetUrl = $this->httpHost . '/' . $this->urls['reset_password'] . '?token=' . $user->kanso_password_key;

        $emailData =
        [
            'name'        => $user->name,
            'resetUrl'    => $resetUrl,
            'websiteName' => $this->domainName,
            'websiteUrl'  => $this->httpHost,
        ];

        if ($user->role === 'administrator' || $user->role === 'writer')
        {
            $emailData['resetUrl'] = $this->httpHost . '/admin/reset-password/?token=' . $user->kanso_password_key;
        }

        // Email credentials
        $senderName   = $this->siteTitle;
        $senderEmail  = 'no-reply@' . $this->domainName;
        $emailSubject = 'Request to reset your password';
        $emailContent = $this->email->html($emailSubject, $this->email->preset('forgot-password', $emailData));
        $emailTo      = $user->email;

        $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

        return true;
    }

    /**
     * Reset password.
     *
     * @access public
     * @param  \kanso\cms\wrappers\User $user User to run request on
     * @return bool
     */
    public function resetPassword(User $user): bool
    {
        $emailData =
        [
            'name'        => $user->name,
            'websiteName' => $this->domainName,
            'websiteUrl'  => $this->httpHost,
            'resetUrl'    => $this->httpHost . '/' . $this->urls['forgot_password'],
            'loginUrl'    => $this->httpHost . '/' . $this->urls['login'],
        ];

        if ($user->role === 'administrator' || $user->role === 'writer')
        {
            $emailData['resetUrl']  = $this->httpHost . '/admin/forgot-password/';
            $emailData['loginUrl']  = $this->httpHost . '/admin/login/';
        }

        // Email credentials
        $senderName   = $this->siteTitle;
        $senderEmail  = 'no-reply@' . $this->domainName;
        $emailSubject = 'Your password was reset at ' . $this->domainName;
        $emailContent = $this->email->html($emailSubject, $this->email->preset('reset-password', $emailData));
        $emailTo      = $user->email;

        $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

        return true;
    }

    /**
     * Forgot username.
     *
     * @access public
     * @param  \kanso\cms\wrappers\User $user User to run request on
     * @return bool
     */
    public function forgotUsername(User $user): bool
    {
        // email variables
        $emailData =
        [
            'name'        => $user->name,
            'username'    => $user->username,
            'websiteName' => $this->domainName,
            'websiteUrl'  => $this->httpHost,
        ];

        // Email credentials
        $senderName   = $this->siteTitle;
        $senderEmail  = 'no-reply@' . $this->domainName;
        $emailSubject = 'Username reminder at ' . $this->domainName;
        $emailContent = $this->email->html($emailSubject, $this->email->preset('forgot-username', $emailData));
        $emailTo      = $user->email;

        $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

        return true;
    }
}
