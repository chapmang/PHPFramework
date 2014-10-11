<?php
namespace Framework\Cache {

	use Framework\Base as Base;

	abstract class Driver extends Base {



		public function initialize() {

			return $this;
		}

		abstract public function get($key);

		abstract public function set($key, $value);

		abstract public function delete($key);

		protected function _getExceptonForImplementation($method) {

			return new \Exception("{$method} method not implemented");
		}
	}
}