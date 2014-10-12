<?php
namespace Framework {

	/*
	|--------------------------------------------------------------------------
	| Framework Constants
	|--------------------------------------------------------------------------
	|
	| Register the constants used by the framework. These are things like file
	| extensions and other information that we want to be able to access with
	| just a simple constant.
	|
	*/

	define('EXT', '.php');
	define('CRLF', "\r\n");
	define('MB_STRING', (int) function_exists('mb_get_info'));

	/*
	|--------------------------------------------------------------------------
	| Setup Error & Exception Handling
	|--------------------------------------------------------------------------
	|
	| Register custom handlers for all errors and exceptions so we
	| can display a clean error message for all errors, as well as do any
	| custom error logging that may be setup by the developer.
	|
	*/

	set_exception_handler(function($e) {
		require_once path('system') . "error" . EXT;
		Error::exception($e);
	});

	set_error_handler(function($code, $error, $file, $line) {
		require_once path('system') . "error" . EXT;
		Error::native($code, $error, $file, $line);
	});

	/*
	|--------------------------------------------------------------------------
	| Report All Errors
	|--------------------------------------------------------------------------
	|
	| By setting error reporting to -1, we essentially force PHP to report
	| every error, and this is guaranteed to show every error on future
	| releases of PHP. This allows everything to be fixed early!
	|
	*/

	error_reporting(-1);

	/*
	|--------------------------------------------------------------------------
	| Require Core Classes
	|--------------------------------------------------------------------------
	|
	| Load in the classes that are used for every request
	| or are simpler to just manually load instead of using the auto-loader.
	|
	*/

	require_once path('system') . "registry" . EXT;
	require_once path('system') . "log" . EXT;
	require_once path('system') . "event" . EXT;
	require_once path('system') . "configuration" . EXT;
	require_once path('system') . "autoloader" . EXT;
	
	/*
	|--------------------------------------------------------------------------
	| Register The Framework Auto-Loader
	|--------------------------------------------------------------------------
	|
	| Register the Autoloader class on the SPL auto-loader stack
	| so it can lazy-load our class files as we need them. This class and
	| method will be called each time a class is needed but has not been
	| defined yet and will load the appropriate file.
	|
	*/

	spl_autoload_register(array('Framework\\Autoloader', 'load'));

	/*
	|--------------------------------------------------------------------------
	| Register Database Connector
	|--------------------------------------------------------------------------
	|
	| Register the connector for linking to database.
	|
	| <code>
	|	// Sample standard query
	|	$res1 = Database::selectOne("SELECT walk_name, walk_location as location FROM walk WHERE walk_id BETWEEN :min AND :max", array(':min' => 1, ':max' => 10));
	|
	|	// Sample expressive query
	|	$res2 = Database::query()->from("walk", array("walk_id, walk_name"))->whereNotIn('walk_id', array(1,2,300))->all();
	|
	|	// Sample transaction (using Closure) executing a standard query
	|	$res3 = Database::transaction(function() {
	|		return Database::selectOne("SELECT walk_name, walk_location as location FROM walk WHERE walk_id BETWEEN :min AND :max", array(':min' => 1, ':max' => 10));	
	|	});
	|
	|	// Sample transaction (using Closure) executing an expressive query
	|	// also demonstrates saving multiple rows
	|	$res4 = Database::transaction(function(){
	|		return Database::query()->from('walk')->save(
	|	 		array(
	|				array('walk_name'=>'tester',
	|				'walk_location' => 'Abergele'),
	| 				array('walk_name'=>'tester',
	| 				'walk_location' => 'Abergele')
	| 			));	
	|		});
	| </code>
	*/


	/*
	|--------------------------------------------------------------------------
	| Register Session Driver
	|--------------------------------------------------------------------------
	|
	| Register the driver for sessions
	| Other drivers can be used if registered with the factory class
	|
	*/

	$session = new Session();
	Registry::set("session", $session->initialize());

	/*
	|--------------------------------------------------------------------------
	| Register Cache Driver
	|--------------------------------------------------------------------------
	|
	| Register the driver for caching
	| Other drivers can be used if registered with the factory class
	|
	*/

	$cache = new Cache();
	Registry::set("cache", $cache->initialize());
	    
	/*
	|--------------------------------------------------------------------------
	| Register Router
	|--------------------------------------------------------------------------
	|
	| Register the Router class to making sure we get to the right place
	|
	*/ 

	$router = new Router(array(
		"method" => RequestMethods::server('REQUEST_METHOD'),
	    "url" => RequestMethods::get('url','home'),
	));
	
	Registry::set("router", $router);

	/*
	|--------------------------------------------------------------------------
	| Register Routes
	|--------------------------------------------------------------------------
	|
	| Register any predefined custom application routes.
	| Can be used to direct URI to specific controller and/or methods
	|
	*/

	include path('app') . 'routes' . EXT;

	/*
	|--------------------------------------------------------------------------
	| Route The Incoming Request
	|--------------------------------------------------------------------------
	|
	| AND NOW THE MAGIC!
	| Route the request to the appropriate route.
	|
	*/

	$router->initialize();

    // unset globals
    unset($database);
    unset($session);
    unset($router);
    
}