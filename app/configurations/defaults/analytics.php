<?php

return
[
	/*
	 * ---------------------------------------------------------
	 * Google Adwords/Analytics Configuration
	 * ---------------------------------------------------------
	 */
	'google' =>
	[
		/*
	 	 * enabled : Enable/disable analytics tracking
	 	 * id      : Google analytics tracking id
	 	 */
		'analytics' =>
		[
			'enabled'  => true,
			'id'       => 'UA-4342443-1',
		],

		/*
	 	 * enabled    : Enable/disable adwords tracking
	 	 * id         : Google adwords tracking id
	 	 * conversion : Google adwords conversion id
	 	 */
		'adwords' =>
		[
			'enabled'    => true,
			'id'         => 'AW-3234432-1',
			'conversion' => '3234432/fdf3423',
		],
	],

	/*
 	 * enabled : Enable/disable Facebook pixel tracking
 	 * pixel   : Facebook pixel tracking id
 	 */
	'facebook' => 
	[
		'enabled' => true,
		'pixel'   => '13434423423432'
	],
];
