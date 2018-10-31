<?php

return
[
	/*
	 * ---------------------------------------------------------
	 * Enable/Disable the CDN
	 * ---------------------------------------------------------
	 *
	 * Enable or disable using the CDN component. The CDN will filter
	 * the HTTP response body before outputting it to the client.
	 */
	'enabled' => false,

	/*
	 * ---------------------------------------------------------
	 * CDN hostname
	 * ---------------------------------------------------------
	 *
	 * The full URL to the CDN host that you are using. For example,
	 * if you have assets hosted at "cdn.example.com" you would put
	 * "http://cdn.example.com". Also don't forget to include "HTTPS"
	 * if you're serving over SSL.
	 */
	'host' => '',
];
