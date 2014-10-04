<?php
namespace Framework {
	
	/**
	 * Event
	 * Manage event listeners used for profiling state changes
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @version 1.0
	 * @package Framework
	 */
	class Event {
		private static $_callbacks = array();

		private function __construct() {
			// do nothing
		}

		private function __clone() {
			// do nothing
		}

		/**
		 * Add callback to array allowing execution on type
		 * @param string $type     Event to be watched
		 * @param method $callback Callback function to be exicuted
		 */
		public static function add($type, $callback) {
			if (empty(self::$_callbacks[$type])) {
				self::$_callbacks[$type] = array();
			}
			self::$_callbacks[$type][] = $callback;
		}

		/**
		 * Trigger an event, optional parameters will be available
		 * to any callbacks executed
		 * @param  string $type       Event type to be fired
		 * @param  array $parameters Array of option parameters for callback
		 */
		public static function fire($type, $parameters = null) {
			if (!empty(self::$_callbacks[$type])) {
				foreach (self::$_callbacks[$type] as $callback) {
					call_user_func_array($callback, $parameters);
				}
			}
		}

		/**
		 * Remove stored callback
		 * @param  string $type     Event to type to be removed
		 * @param  method $callback Callback to be removed
		 */
		public static function remove($type, $callback) {
			if (!empty(self::$_callbacks[$type])) {
				foreach ($self::$_callbacks as $i => $found) {
					if ($callback == $found) {
						unset(self::$_callbacks[$type][$i]);
					}
				}
			}
		}
	}
}