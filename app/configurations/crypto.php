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
	'default' => 'openssl',

	/*
	 * ---------------------------------------------------------
	 * Configurations
	 * ---------------------------------------------------------
	 *
	 * The supported cryptography libraries are: "openssl".
	 *
	 * library: Cryptography library you want to use.
	 * cipher : The cipher method to use for encryption.
	 * key    : Key used to encrypt/decrypt data. You should NOT use the key included with the framework in a production environment!
	 */
	'configurations' =>
	[
		'openssl' =>
		[
			'library'  => 'openssl',
			'cipher'   => 'AES-256-ECB',
			'key'      => '6eedbbb1c2680921e324889ade0187322b5c4e24896bc824dc50559b19cd9ea5',
		],
	],
];
