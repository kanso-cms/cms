<?php

return
[
	/*
	 * ---------------------------------------------------------
	 * Enable/Disable HTTP caching
	 * ---------------------------------------------------------
	 *
	 * Enable or disable using the HTTP Cache component.
	 * HTTP caching is used to cache the HTTP response body over multiple requests.
	 */
	'http_cache_enabled' => false,

	/*
	 * ---------------------------------------------------------
	 * Default
	 * ---------------------------------------------------------
	 *
	 * Default configuration to use.
	 */
	'default' => 'file',

	/*
	 * ---------------------------------------------------------
	 * Configurations
	 * ---------------------------------------------------------
	 *
	 * You can define as many caching configurations as you want.
	 *
	 * The supported session types are: "file".
	 *
	 * type   : The storage implementation to use.
	 * expire : A valid unix timestamp of the max age of any item from now.
	 * path   : Save path for cached files (only required when using "file" caching).
	 */
	'configurations' =>
	[
		'file' =>
		[
			'type'   => 'file',
			'expire' => strtotime('+1 week'),
			'path'   => APP_DIR . '/storage/cache',
		],
	],
];
