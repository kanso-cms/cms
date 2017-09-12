<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

/**
 * curl Helper
 *
 * @author Joe J. Howard
 */
class Curl
{
	/**
	 * Send a CURL request to a url
	 *
     * @access public
	 * @param  string $url         The url to request to
	 * @param  arary  $headers     Any additional headers to send (optional) (default [])
	 * @param  array  $postData    POST fields (optional) (default [])
	 * @param  string $auth        HTTP 'Authorization' header (optional) (default '')
	 * @param  string $contentType HTTP content type header (optional) (default 'application/json')
	 * @param  string $accept      HTTP accept header (optional) (default 'application/json')
	 * @return false|array
	 */
	public static function post(string $url, array $headers = [], array $postData = [], $auth = '', string $contentType = 'application/json', $accept = 'application/json')
	{
		# Headers
		$_headers =
		[
			'Content-Type : '.$contentType,
			'Accept : '.$accept,
		];

		if ($auth)
		{
			$_headers[]= 'Authorization : '.$auth;
		}

		$headers = array_merge($_headers, $headers);

		# Options
		$options =
		[
			CURLOPT_POST           => true,
		   	CURLOPT_RETURNTRANSFER => true,
		   	CURLOPT_HTTPHEADER     => $headers,
		];
		if ($postData)
		{
			$options[CURLOPT_POSTFIELDS] = json_encode($postData);
		}

		# Curl init
		$handle = curl_init($url);

		# Add options
		curl_setopt_array($handle, $options);

		# Send the request
		$response = curl_exec($handle);

		# Check for errors
		if ($response === false)
		{
		    return false;
		}

		# Decode the response
		$response = json_decode($response, true);

		if (json_last_error() == JSON_ERROR_NONE)
		{
			return $response;
		}

		return false;
	}

}