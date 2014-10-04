<?php
namespace Framework\Logs {

	use Framework\Base as Base;

	class Writer extends Base {

		public function initialize() {
			
            return $this;
        }
        
        protected function _sum($values) {
			$count = 0;
			foreach ($values as $value) {
				$count += $value;
			}
		return $count;
		}

		protected function _average($values) {
			return $this->_sum($values) / sizeof($values);
		}
        
        protected function _getExceptionForImplementation($method) {
            return new \Exception("{$method} method not implemented", 500);
        }

	}
}