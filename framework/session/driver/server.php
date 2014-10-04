<?php
namespace Framework\Session\Driver {

    use Framework\Session as Session;
	/**
	 * Server
	 * Class for getting and setting session values
	 * @version  1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 * @subpackage Session
	 */
	class Server extends Session\Driver {

		/**
		 * Optional prefix for session names
		 * @var string
		 * @readwrite
		 */
		protected $_prefix = "app_";


		/**
		 * Apply options array to correct parameters
		 * and start session;
		 * @param array $options Driver options
		 */
		public function __construct($options = array()) {
			parent::__construct($options);
			session_start();
		}

		/**
		 * Get session value
		 * @param  string $key     Name of session value to get
		 * @param  string $default Default to return if no value
		 * @return string          Value of session variable
		 */
		public function get($key, $default = null) {
			$prefix = $this->_prefix;
			if (isset($_SESSION[$prefix.$key])) {
				return $_SESSION[$prefix.$key];
			}
			return $default; 
		}

		/**
		 * Set session vlaue
		 * @param string $key   Session value to set
		 * @param string $value Value of to be set
		 */
		public function set($key, $value) {
			$prefix = $this->_prefix;
			$_SESSION[$prefix.$key] = $value;
			return $this;
		}

		/**
		 * Remove session value
		 * @param  string $key Session value to be erased
		 */
		public function erase($key) {
			$prefix = $this->_prefix;
			unset($_SESSION[$prefix.$key]);
			return $this;
		}

		
		public function __destruct() {
			session_write_close();
		}
	}
}