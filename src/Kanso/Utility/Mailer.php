<?php

namespace Kanso\Utility;

class Mailer
{
	/**
	 * Send Kanso's default email
	 *
	 * This function sends HTML emails with a default body. 
	 * The idea here is to have a single function that sends emails throughout
	 * the application to ensure consistency.
	 *
	 * @param  string    $emailTo         The email address to send the email to
	 * @param  string    $emailFrom       The name of the sender
	 * @param  string    $emailSender     The email address of the sender
	 * @param  string    $emailSubject    The subject of the email
	 * @param  string    $emailMessage    The message to be sent
	 * @return bool
	*/
	public static function sendHTMLEmail($emailTo, $emailFrom, $emailSender, $emailSubject, $emailMessage) 
  	{
  		$data = [
  			'subject' => $emailSubject,
  			'message' => $emailMessage,
  		];
  		$email_body      = \Kanso\Templates\Templater::load('EmailBody', $data);
	    $email_headers   = 'MIME-Version: 1.0' . "\r\n";
	    $email_headers  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	    $email_headers  .= 'From: '.$emailSender.' <'.$emailFrom.'>' . "\r\n";

	    # Fire the email event
		\Kanso\Events::fire('htmlEmailSend', [$emailTo, $emailFrom, $emailSender, $emailSubject, $emailMessage]);

		# Filter the email body
		$email_body   = \Kanso\Filters::apply('emailBody', $email_body);

	    return mail($emailTo, $emailSubject, $email_body, $email_headers);
  	}
}