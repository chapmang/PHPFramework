<?php
namespace Framework {
	
	/**
	 * Registry
	 * Registry of stored class instances to help
	 * minimize reinitialization of common classes
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 */
	class Registry {

		/**
		 * Instances stored in Registry
		 * @var array
		 */
		private static $_instances = array();


		private function __construct() {} // Locked
		private function __clone() {} // Locked
		
		/**
		 * Return instance of matching key
		 * @param  String $key     Instance to be returned
		 * @param  String $default Value returned if instanc edoes not exist
		 */
		public static function get($key, $default = null) {
			if (isset(self::$_instances[$key])) {
				return self::$_instances[$key];
			}
			return $default;
		}

		/**
		 * Store instance with given key
		 * @param String $key      Key of stored instance
		 * @param Class $instance  Instance of class to be stored
		 */
		public static function set($key, $instance = null) {
			self::$_instances[$key] = $instance;
		}

		/**
		 * Remove specific instance
		 * @param  String $key Key of instance to be removed
		 */
		public static function erase($key) {
			unset(self::$_instances[$key]);
		}
		
	}
}