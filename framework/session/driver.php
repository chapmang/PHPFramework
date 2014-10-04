<?php

namespace Framework\Session
{
    use Framework\Base as Base;
    
    class Driver extends Base
    {
        public function initialize()
        {
            return $this;
        }
        
        protected function _getExceptionForImplementation($method)
        {
            return new \Exception("{$method} method not implemented");
        }
    }
}