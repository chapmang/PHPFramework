<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Error Logging
	|--------------------------------------------------------------------------
	|
	| When error logging is enabled, the framework will log all errors and
	| exceptions. A custom location for log files can be defined below
	|
	*/

	'log' => true,

	/*
	|--------------------------------------------------------------------------
	| Log Location
	|--------------------------------------------------------------------------
	|
	| When error logging is enabled the path below is used to define a custom
	| location for the logs, otherwise the frameworks default location is used. 
	|
	 */
	
	'logPath' => path('storage') . "logs/errors/" . date("Y-m-d") . ".log",

	/*
	|--------------------------------------------------------------------------
	| Error Detail
	|--------------------------------------------------------------------------
	|
	| Detailed error messages contain information about the file in which an
	| error occurs, as well as a PHP stack trace containing the call stack.
	| NB: Advisable to disable in a production environment
	|
	*/

	'detail' => true,

	/*
	|--------------------------------------------------------------------------
	| Ignored Error Levels
	|--------------------------------------------------------------------------
	|
	| Specify the error levels that should be ignored by the error handler. 
	| These levels will still be logged; however, no information about them 
	| will be displayed.
	|
	*/

	'ignore' => array()





);