<?php

return array(
	/*
	|--------------------------------------------------------------------------
	| Default Database Connection
	|--------------------------------------------------------------------------
	|
	| Default database connection. This connection will be used
	| as the default for all database operations unless a different name is
	| given when performing said operation. This connection name should be
	| listed in the array of connections below.
	|
	*/

	'default' => 'mysql',

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| All of the database connections used by your application. Many of your
	| applications will no doubt only use one connection; however, you have
	| the freedom to specify as many connections as you can handle.
	|
	| All database work in Laravel is done through the PHP's PDO facilities,
	| so make sure you have the PDO drivers for your particular database of
	| choice installed on your machine.
	|
	*/

	'connections' => array(

		'sqlite' => array(
			'driver'   => 'sq',
			'database' => '',
			'prefix'   => '',
		),

		'mysql' => array(
			'driver'   => 'mysql',
			'host'     => '',
			'port'	   => '',
			'database' => '',
			'username' => '',
			'password' => '',
			'charset'  => 'utf8',
			'prefix'   => ''
		),

		'pgsql' => array(
			'driver'   => '',
			'host'     => '',
			'database' => '',
			'username' => '',
			'password' => '',
			'charset'  => '',
			'prefix'   => '',
			'schema'   => ''
		),

		'sqlsrv' => array(
			'driver'   => '',
			'host'     => '',
			'database' => '',
			'username' => '',
			'password' => '',
			'prefix'   => ''
		),

		'oracle' => array(
			"driver" => 'oracle',
			'protocol' => '',
			'host' => '',
			'port' => '',
			'sid' => '',
			'username' => '',
			'password' => '',
			'charset' => '',
			'prefix' => ''
		)
	),

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may wish to retrieve records as arrays
	| instead of objects. Here you can control the PDO fetch style of the
	| database queries run by your application.
	|
	*/

	'fetch' => PDO::FETCH_OBJ,


);
