<?php
namespace Framework {

	use Framework\RequestMethods as Requestmethods;
	use FrameworkConfiguration;

	class Cookie extends Base {

		public static function exists($key) {

			return ! is_null(static::get($key));
		}

		public static function get($key, $default="") {

			if (!is_null($value = RequestMethods::cookie($key))) {
				return static::parse($value);
			}

			return $default;
		}

		public static function set($key, $value, $duration = 0, $path = "/", $domain = null, $secure = false) {
			
			if ( $duration !== 0) {
				$expire = time() + ($duration * 60);
			}
			
			$cookieData = static::encode($value);
			if (is_array($cookieData)) {
				foreach ($cookieData as $k => $v) {
					static::setCookieData($k, $v, $expire, $path, $secure);
				}
			} else {
				static::setCookieData($key, $cookieData, $expire, $path, $secure);
			}
		}

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

		protected static function setCookieData($key, $value, $expire, $path, $secure) {

			return setcookie($key, $value, $expire, $path, $secure);
		}

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

		protected static function hash($value){
			return hash_hmac('sha1', $value, Configuration::get('application.key'));
		}
	}
}
