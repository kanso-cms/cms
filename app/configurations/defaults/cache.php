<?php

return
[
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
	 * The supported caching types are: "file".
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
			'expire' => '+1 week',
			'path'   => APP_DIR . '/storage/cache',
		],
	],

	/*
	 * ---------------------------------------------------------
	 * Enable/Disable HTTP caching
	 * ---------------------------------------------------------
	 *
	 * http_cache_enabled : Enable or disable using the HTTP Cache component.
	 * http_max_age       : Max age in seconds you want to the client’s browser to cache a response
	 */
	'http_cache_enabled' => false,
	'http_max_age'       => 3600,
];
