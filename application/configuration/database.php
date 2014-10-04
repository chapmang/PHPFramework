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
			'host'     => 'localhost',
			'port'	   => '3306',
			'database' => 'aamedia-dev',
			'username' => 'aamedia_admin',
			'password' => 'ag3D745e453U83B',
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
			'protocol' => 'TCP',
			'host' => '10.78.16.161',
			'port' => '1521',
			'sid' => 'DACART01',
			'username' => 'gb100',
			'password' => 'gb100',
			'charset' => 'AL32UTF8',
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
