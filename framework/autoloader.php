<?php
namespace Framework {

    /**
     * Autoloader
     * Handles autoloading of any requested classes
     * @version 1.0
     * @author Geoff Chapman <geoff.chapman@mac.com>
     * @package Framework
     */
	class Autoloader {
        
        /**
         * The static routes required by the framework
         * @var array
         */
		private static $_paths = array(
            "libraries",
            "controllers",
            "models",
            ""
        );

        /**
         * Set include paths from $_paths array,
         * build file name using include path, search 
         * and if found include
         * @param  string $class The class name being searched for
         */
        public static function load($class) {
            
             // fix extra backslashes in $_POST/$_GET
            if (get_magic_quotes_gpc()) {
                $globals = array("_POST", "_GET", "_COOKIE", "_REQUEST", "_SESSION");
                
                foreach ($globals as $global) {
                    if (isset($GLOBALS[$global])) {
                        $GLOBALS[$global] = self::_clean($GLOBALS[$global]);
                    }
                }
            }

            $paths = array_map(function($item) {
                return path('app') . $item;
            }, self::$_paths);

            $paths[] = get_include_path();
            set_include_path(join(PATH_SEPARATOR, $paths));
            
            $paths = explode(PATH_SEPARATOR, get_include_path());
            $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
            $file = strtolower(str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\"))).".php";

            foreach ($paths as $path) {
                $combined = $path.DIRECTORY_SEPARATOR.$file;
                
                if (file_exists($combined)) {
                    include($combined);
                    return;
                }
            }
            $paths = array();
            throw new \Exception("{$class} not found");
        }

        /**
         * Clean up slashes
         * @param  array $array
         * @return array       
         */
        protected static function _clean($array) {

            if (is_array($array)) {
                return array_map(__CLASS__."::_clean", $array);
            }
            return stripslashes($array);
        }
	}
}