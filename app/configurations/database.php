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
	'default' => 'kanso',

	/*
	 * ---------------------------------------------------------
	 * Configurations
	 * ---------------------------------------------------------
	 *
	 * You can define as many database configurations as you want.
	 *
	 * dsn        : PDO dsn string (will override other options if provided)
	 * username   : (optional) Username of the database server
	 * password   : (optional) Password of the database server
	 * persistent : (optional) Set to true to make the connection persistent
	 * log_queries: (optional) Enable query logging?
	 * reconnect  : (optional) Should the connection automatically be reestablished?
	 * options    : (optional) An array of PDO options
	 * queries    : (optional) Queries that will be executed right after a connection has been made
	 */
	'configurations' =>
	[
		'kanso' =>
		[
			'name'     => 'Kanso',
			'host' 	   => 'localhost',
			'username' => 'root',
			'password' => 'root',
			'table_prefix' => 'kanso_',
			'options'  =>
			[
				'MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES utf8',
				'ATTR_DEFAULT_FETCH_MODE' => \PDO::FETCH_ASSOC,
			],
		],
	],
];
