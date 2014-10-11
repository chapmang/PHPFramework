<?php
return array(
	/*
	|--------------------------------------------------------------------------
	| Default Cache Service
	|--------------------------------------------------------------------------
	|
	| Default cache service. This service will be used as the default
	| caching mechanism by the framework in order to help speed and reduce
	| request to external services including databases
	|
	*/
	'default' => 'file',

	/*
	|--------------------------------------------------------------------------
	| Cache Service
	|--------------------------------------------------------------------------
	|
	| All the setting for caching service used by the framework. May application
	| will no doubt only use one cache mechanism; however multiple service are supported
	|
	*/

	'settings' => array(
		'file' => array(
			'duration' => 3600,
			'cacheFolder' => path('storage').'cache/',
			'cacheFile' => date("Y-m-d")
			),
		'memcached' => array(
			'duration' => 3600,
			'host' => '127.0.0.1',
			'port' => '11211'
			),
		'wincache' => array(
			)
		)
);