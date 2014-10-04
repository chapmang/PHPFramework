<?php

namespace Framework {

	/**
	 * Request Methods
	 * Utility class accessing superglobal request methods
	 * @version  1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework 
	 */
	class RequestMethods {

		private function __construct() {
			// Do nothing
		}

		private function __clone() {
			// Do nothing
		}
	
		/**
		 * Return get variable based on key
		 * 
		 * @param  string $key     Get variable key
		 * @param  string $default Default value to return
		 * @return mixed           Get variable being returned
		 */
		public static function get($key, $default = "") {

			if (!empty($_GET[$key])) {
				return trim($_GET[$key]);
			}
			return $default;
		}

		/**
		 * Return post variable based on key
		 * 
		 * @param  string $key     Post variable key
		 * @param  string $default Default value to return
		 * @return mixed           Post variable being returned
		 */
		public static function post($key, $default = "") {

			if(!empty($_POST[$key])) {
				return trim($_POST[$key]);
			}
			return $default;
		}

		/**
		 * Return the JSON payload request, used wjson data is passed throught the http body
		 * 
		 * @param  boolean $asArray Is the data wanted as an associative array
		 * @return object           
		 */
		public static function json($asArray = false) {

			return json_decode(file_get_contents('php://input'), $asArray);
		}

		/**
		 * Return server variable based on key
		 * 
		 * @param  string $key     Server variable key
		 * @param  string $default Default value to return
		 * @return mixed           Server variable being returned
		 */
		public static function server($key, $default = "") {

            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
            return $default;
        }
		
		/**
		 * Return cookie variable based on key
		 * 
		 * @param  string $key     Cookie variable key
		 * @param  string $default Default value to return
		 * @return mixed           Cookie variable being returned
		 */
        public static function cookie($key, $default = "") {
        	
            if (!empty($_COOKIE[$key])) {
                return $_COOKIE[$key];
            }
            return $default;
        }

        /**
         * Return uploaded file/s
         * @param  string $key     Form field name for upload
         * @param  string $default Default upload vale
         * @return uploadedFile    
         */
        public static function file($key = null, $default = "") {

        	if (!isset($_FILES[$key]['error']) ||
        		is_array($_FILES[$key]['error'])) {
        		return false;
        	}
        	
        	return $_FILES[$key]; 
        }

	}
}