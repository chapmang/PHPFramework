<?php

/*
|--------------------------------------------------------------
| The path to the application directory.
|--------------------------------------------------------------
*/
$paths['app'] = 'application';

/*
|--------------------------------------------------------------
| The path to the framework directory.
|--------------------------------------------------------------
*/
$paths['system'] = 'framework';

/*
|--------------------------------------------------------------
| The path to the storage directory.
|--------------------------------------------------------------
*/
$paths['storage'] = 'storage';

/* 
|--------------------------------------------------------------
| The path to the public directory.
|--------------------------------------------------------------
*/
$paths['public'] = 'public';


/**************************************************
 ************ END OF USER CONFIGURATION ***********
 **************************************************/

/*
|--------------------------------------------------------------
| Change to the current working directory.
|--------------------------------------------------------------
*/
chdir(__DIR__);

/*
|--------------------------------------------------------------
| Define the directory separator for the environment.
|--------------------------------------------------------------
*/
if ( ! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/*
|--------------------------------------------------------------
| Define the path to the base directory.
|--------------------------------------------------------------
*/
$GLOBALS['framework_paths']['base'] = __DIR__.DS;

/*
|--------------------------------------------------------------
| Define each constant if it hasn't been defined.
|--------------------------------------------------------------
*/
foreach ($paths as $name => $path) {
    if ( ! isset($GLOBALS['framework_paths'][$name])) {
        $GLOBALS['framework_paths'][$name] = realpath($path).DS;
    }
}

/**
 * A global path helper function.
 * <code>
 *     $storage = path('storage');
 * </code>
 * 
 * @param  string  $path
 * @return string
 */
function path($path) {
  return $GLOBALS['framework_paths'][$path];
}

/**
 * A global path setter function.
 * @param  string  $path
 * @param  string  $value
 * @return void
 */
function set_path($path, $value) {
  $GLOBALS['framework_paths'][$path] = $value;
}