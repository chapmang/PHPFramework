<?php
namespace Framework {

	/**
	 * Redirect
	 * Helper class for simplifying page redirection
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 */
	class Redirect {

		private function __construct(){}

		private function __clone(){}

		public static function home($status = 303) {
			header('Location:' . URL , true, $status);
			die();
		}

		public static function to($url, $status = 303) {
			header('Location:' . URL . $url, true, $status);
			die();
		}

	}
}