<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\email\utility;

use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Email sender utility.
 *
 * @author Joe J. Howard
 */
class Sender
{
    /**
     * SMTP mail utility.
     *
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    private $smtpMailer;

    /**
     * Send mail via SMTP.
     *
     * @var bool
     */
    private $useStmp;

    /**
     * SMTP mail configuration.
     *
     * @var array
     */
    private $smtpSettings;

    /**
     * Constructor.
     *
     * @access public
     * @param \PHPMailer\PHPMailer\PHPMailer $smtpMailer   SMTP mail utility
     * @param bool                           $useStmp      Use SMTP to send emails (optional) (default false)
     * @param array                          $smtpSettings SMTP setings
     */
    public function __construct(PHPMailer $smtpMailer, bool $useStmp = false, array $smtpSettings = [])
    {
        $this->smtpMailer = $smtpMailer;

        $this->useStmp = $useStmp;

        $this->smtpSettings = $smtpSettings;
    }

    /**
     * Send an HTML or plain text email.
     *
     * @access public
     * @param  string $toEmail     The email address to send the email to
     * @param  string $senderName  The name of the sender
     * @param  string $senderEmail The email address of the sender
     * @param  string $subject     The subject of the email
     * @param  string $content     The message to be sent
     * @param  string $format      html or plain
     * @return bool
     */
    public function send(string $toEmail, string $senderName, string $senderEmail, string $subject, string $content, string $format = 'html'): bool
    {
        if ($this->useStmp === true && !empty($this->smtpSettings))
        {
            $mail = $this->configureSmtp();

            $mail->clearAllRecipients();
            $mail->clearAttachments();
            $mail->clearCustomHeaders();

            $mail->Subject = $subject;
            $mail->addReplyTo($senderEmail, $senderName);
            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($toEmail);

            if ($format === 'html')
            {
                $mail->isHTML(true);
                $mail->msgHTML($content);
            }
            else
            {
                $content = nl2br($content);
                $mail->isHTML(false);
                $mail->Body    = $content;
                $mail->AltBody = $content;
            }

            return boolval($mail->send());
        }
        else
        {
            if ($format === 'html')
            {
                $headers   = 'MIME-Version: 1.0' . "\r\n";
                $headers  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers  .= 'From: ' . $senderEmail . ' <' . $senderName . '>' . "\r\n";
            }
            else
            {
                $headers = 'From: ' . $senderEmail . ' <' . $senderName . '>' . "\r\n";
            }

            return boolval(mail($toEmail, $subject, $content, $headers));
        }
    }

    /**
     * Configure SMTP settings.
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    private function configureSmtp(): PHPMailer
    {
        $mail = $this->smtpMailer;
        $mail->isSMTP();
        $mail->SMTPDebug  = $this->smtpSettings['debug'];
        $mail->Host       = $this->smtpSettings['host'];
        $mail->Port       = $this->smtpSettings['port'];
        $mail->SMTPSecure = $this->smtpSettings['secure'];
        $mail->SMTPAuth   = $this->smtpSettings['auth'];

        if (isset($this->smtpSettings['auth_type']) && $this->smtpSettings['auth_type'] === 'XOAUTH2')
        {
            $mail->AuthType   = 'XOAUTH2';
            $mail->setOAuth($this->getSmtpOauth());
        }
        else
        {
            $mail->Username   = $this->smtpSettings['username'];
            $mail->Password   = $this->smtpSettings['password'];
        }

        return $mail;
    }

    /**
     * Get STMP Oauth.
     *
     * @access private
     * @return \PHPMailer\PHPMailer\OAuth
     */
    private function getSmtpOauth(): OAuth
    {
        $config =
        [
            'provider'     => $this->getGoogleOAuth(),
            'clientId'     => $this->smtpSettings['client_id'],
            'clientSecret' => $this->smtpSettings['client_secret'],
            'refreshToken' => $this->smtpSettings['refresh_token'],
            'userName'     => $this->smtpSettings['username'],
        ];

        return new OAuth($config);
    }

    /**
     * Get Google OAuth Wrapper.
     *
     * @access private
     * @return \League\OAuth2\Client\Provider\Google
     */
    private function getGoogleOAuth(): Google
    {
        $config =
        [
            'clientId'     => $this->smtpSettings['client_id'],
            'clientSecret' => $this->smtpSettings['client_secret'],
        ];

        return new Google($config);
    }
}
