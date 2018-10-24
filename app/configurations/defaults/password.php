<?php

return
[
	/*
	 * ---------------------------------------------------------
	 * Default
	 * ---------------------------------------------------------
	 *
	 * Default password encryption configuration to use.
	 */
	'default' => 'nativePHP',

	/*
	 * ---------------------------------------------------------
	 * Configurations
	 * ---------------------------------------------------------
	 *
	 * The supported password hashing libraries are: "nativePHP".
	 *
	 * library : Password hashing library you want to use.
	 * algo    : The encryption algorithm to use
	 */
	'configurations' =>
	[
		/*
		 * Encrypt using PHP's native password hashing functions
		 */
		'nativePHP' =>
		[
			'library' => 'nativePHP',
			'algo'    => PASSWORD_DEFAULT,
		],
	],
];
