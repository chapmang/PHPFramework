<?php

namespace Framework\Router
{
    use Framework\Base as Base;
    
    class Route extends Base {
        /**
        * @readwrite
        */
        protected $_pattern;
        
        /**
        * @readwrite
        */
        protected $_controller;
        
        /**
        * @readwrite
        */
        protected $_action;

        /**
         * @readwrite
         */
        protected $_method;
        
        /**
        * @readwrite
        */
        protected $_parameters = array();
        
        public function _getExceptionForImplementation($method) {
            return new \Exception ("{$method} method not implemented");
        }

    }
}