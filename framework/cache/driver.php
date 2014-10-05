<?php
namespace Framework\Cache {

	use Frameework\Base as Base;

	abstract class Driver extends Base {



		public function initialize() {

			return $this;
		}

		abstract public function get();

		abstract public function set();

		abstract public function delete();

		protected function _getExceptonForImplementation($method) {

			return new \Exception("{$method} method not implemented");
		}
	}
}