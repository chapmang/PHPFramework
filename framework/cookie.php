<?php
namespace Framework {

	use Framework\RequestMethods as Requestmethods;
	use FrameworkConfiguration;

	class Cookie extends Base {

		/**
		 * Test if a given cookie exists
		 * @param  string $key Name of Cookie to be searched for
		 * @return boolean     
		 */
		public static function exists($key) {

			return ! is_null(static::get($key));
		}

		/**
		 * Get the value of a given cookie
		 * @param  string $key     Name of cookie to be retrieved
		 * @param  string $default 
		 * @return mixed          
		 */	
		public static function get($key, $default="") {

			if (!is_null($value = RequestMethods::cookie($key))) {
				return static::parse($value);
			}

			return $default;
		}

		/**
		 * Set the value of a given cookie
		 * @param string  $key      Name of cookie to be set
		 * @param string  $value    Value of cookie to be set
		 * @param integer $duration Life span of cookie in minutes
		 * @param string  $path     The path on the server the cookie will be available on
		 * @param string  $domain   The domain the cookie will be available to
		 * @param boolean $secure   Indicates if cookie is https only
		 */
		public static function set($key, $value, $duration = 0, $path = "/", $domain = null, $secure = false) {
			
			// convert life span to unix timestamp
			if ( $duration !== 0) {
				$expire = time() + ($duration * 60);
			}
			
			// Encode the data for storage in the cookie
			$cookieData = static::encode($value);

			if (is_array($cookieData)) {
				foreach ($cookieData as $k => $v) {
					static::setCookieData($k, $v, $expire, $path, $secure);
				}
			} else {
				static::setCookieData($key, $cookieData, $expire, $path, $secure);
			}
		}

		/**
		 * Encode the data being stored in the cookie
		 * @param  string $value date to be encoded
		 * @return mixed        
		 */
		protected  static function encode($value = null) {

			if (is_array($value)) {
				foreach ($value as $k => $v) {
					$encoded[$k] = static::encode($v);
				}
			return $encoded;
			} else {
				return static::hash($value)."+".$value;
			}
		}

		/**
		 * Add encoded date to valid cookie
		 * @param string  $key    Name of cookie
		 * @param string  $value  Data to be stored
		 * @param integer $expire Life span of cookie
		 * @param string  $path     The path on the server the cookie will be available on
		 * @param string  $domain   The domain the cookie will be available to
		 * @param boolean $secure   Indicates if cookie is https only
		 */
		protected static function setCookieData($key, $value, $expire= 0, $path = "/", $domain = null, $secure = false) {

			return setcookie($key, $value, $expire, $path, $domain, $secure);
		}

		/**
		 * Parse the value of a retrieved cookie
		 * @param  string $value Value to be parsed
		 * @return mixed        
		 */
		protected static function parse($value) {

			$sections = explode('+', $value);

			if (!(count($sections) >= 2)) {

				return null;
			}

			if ($sections[0] !== static::hash($sections[1])) {

				return null;
			}

			return $sections[1];
		}

		/**
		 * Hash a given string
		 * @param  string $value Value to be hashed
		 * @return string        
		 */
		protected static function hash($value){
			return hash_hmac('sha1', $value, Configuration::get('application.key'));
		}
	}
}
