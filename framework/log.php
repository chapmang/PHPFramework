<?php
namespace Framework {

	/**
	 * Log
	 * Class for writing to log files
	 * @version  1.0
	 * @author  Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 */
	class Log {

		protected static $_logPath;

		private function __construct() {
			// do nothing
		}

		private function __clone() {
			// do nothing
		}

		/**
		 * Log an exception
		 * @param Exception $e 
		 * @return void 
		 */
		public static function exception($e) {

			// Fetch log location from config file 
			if (Configuration::get('error.logPath')) {
				self::$_logPath = Configuration::get('error.logPath');
			} else {
				// Default framework log location 
				self::$_logPath = path('storage') . "logs/errors/" . date("Y-m-d") . ".log";
			}

			// Write the entry to the log file
			$message = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
			
			static::write('error', $message);
		}

		/**
		 * Write a message to the log file.
		 * @param string $type    Type of log entry
		 * @param string $logFile URL of logfile
		 * @param string $message Formated log message
		 * @return void           	
		 */
		public static function write($type, $message) {

			// Write the entry to the log file
			$type = strtoupper($type);
			$template = "[".date('Y-m-d H:i:s')."] %s - %s".PHP_EOL;
			$logEntry = sprintf($template, $type, $message);
			file_put_contents(self::$_logPath, $logEntry, FILE_APPEND);

		}

		/**
		 * Magic method allowing dynamic log calls
		 * <code>
		 *		// Write an "error" message to the log file
		 *		Log::error('This is an error!');
		 *
		 *		// Write a "warning" message to the log file
		 *		Log::warning('This is a warning!');
		 *
		 *		// Log an arrays data
		 *		Log::info(array('name' => 'Sawny', 'passwd' => '1234', array(1337, 21, 0)), true);
		 *      //Result: Array ( [name] => Sawny [passwd] => 1234 [0] => Array ( [0] => 1337 [1] => 21 [2] => 0 ) )
		 *      //If we had omit the second parameter the result had been: Array
		 * </code>
		 */
		public static function __callStatic($method, $parameters) {
			
			$parameters[1] = (empty($parameters[1])) ? false : $parameters[1];
			static::write($method, $parameters[0], $parameters[1]);
		}

		
	}
}