<?php

use kanso\Kanso;
use League\OAuth2\Client\Provider\Google;

/*
 * ---------------------------------------------------------
 * SMTP Oauth token generator
 * ---------------------------------------------------------.
 *
 */

/*
 * Generate Oauth token for google SMTP XOAUTH
 *
 * Sends GET requests to
 */
$kanso->Router->get('/get-oauth-token/', function($request, $response, $next, $config)
{
	$queries  = $request->queries();
	$provider = new Google([
	    'clientId'     =>  $config->get('email.smtp_settings.client_id'),
	    'clientSecret' =>  $config->get('email.smtp_settings.client_secret'),
	    'redirectUri'  =>  $request->environment()->HTTP_HOST . '/get-oauth-token',
	    'accessType'   => 'offline',
	]);

	if (!empty($queries['error']))
	{
	    // Got an error, probably user denied access
	    throw new Exception('Got error: ' . htmlspecialchars($queries['error'], ENT_QUOTES, 'UTF-8'));
	}
	// If we don't have an authorization code then get one
	elseif (empty($queries['code']))
	{
	    $response->session()->set('oauth2state', $provider->getState());
	    $response->redirect($provider->getAuthorizationUrl(['scope' => ['https://mail.google.com/']]));
	}
	elseif (empty($queries['state']))
	{
	    throw new Exception('Invalid state.');
	}
	else
	{
	    // Try to get an access token (using the authorization code grant)
	    $token = $provider->getAccessToken('authorization_code',
	    [
	        'code' => $queries['code'],
	    ]);

	    $refresh_token =  $token->getRefreshToken();

	    $response->body()->set('Refresh Token : ' . $refresh_token);

	    $config->set('email.smtp_settings.refresh_token', $refresh_token);

	    $config->save();

	}
}, Kanso::instance()->Config);
