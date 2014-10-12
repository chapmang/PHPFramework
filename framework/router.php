<?php
namespace Framework {

    use Framework\Base as Base;
    use Framework\Event as Event;
    
    /**
    * Router
    * Using the requested URL determine correct 
    * controller/action to execute.
    * All routes must be defined in the application routes file
    * @version 2.0
    * @author Geoff Chapman <geoff.chapman@mac.com>
    * @package Framework
    */      
    class Router extends Base {

        /**
         * The URL to be loaded
         * @var array
         * @readwrite
         */
        protected $_url;

        /**
         * [$_method description]
         * @var [type]
         * @readwrite
         */
        protected $_method;

        /**
         * Controller to be loaded
         * @var string
         * @read
         */
        protected $_controller;

        /**
         * @read
         */
        protected $_action;

        /**
         * Name of default controller
         * @var string
         * @readwrite
         */
        protected $_defaultController = "index";

        /**
         * Name of default action
         * @var string
         * @readwrite
         */
        protected $_defaultAction = "index";

        /**
         * Array of (predefined) routes added
         * @var array
         */
        protected $_routes = array(
            'GET' => array(),
            'POST' => array(),
            'PUT' => array(),
            'DELETE' => array(),
        );

        /**
         * Array of HTTP request methods
         * @var array
         */
        protected $_requestMethods = array(
            'GET',
            'POST',
            'PUT',
            'DELETE'
        );

        /**
         * Add a route to the defined routes array
         * @param Route $route Route object for defining 
         */
        public function addRoute($route) {

            // Does registered route contain more than one request method
            if (is_array($route->method)) {

                foreach ($route->method as $value) {
                    $routeClone = clone $route;
                    $routeClone->method = $value;
                    $this->addRoute($routeClone);
                }
            } else {
                if (in_array($route->method, $this->_requestMethods)) {
                    $this->_routes[$route->method][] = $route; 
                }
            }
            return $this;
        }
        
        /**
         * Remove a route from the defined routes array
         * @param  Route $route Route object to be removed
         * @return Router
         */
        public function removeRoute($route) {

            foreach ($this->_routes as $routeMethod) {
                foreach ($routeMethod as $i => $stored) {
                    if ($stored == $route) {
                        unset($this->_routes[$routeMethod][$i]);
                    }
                }
            }
            return $this;
        }
        
        /**
         * Get all the defined routes
         * @return array The defined routes
         */
        public function getRoutes() {

            $list = array();
            foreach ($this->_routes as $routeMethod) {
                foreach ($routeMethod as $route) {
                    $list[$route->pattern] = get_class($route);
                }
            }
            return $list;
        }

        /**
         * Fetch the URL and try and load a
         * route from it
         * @return boolean
         */
        public function initialize() {

            // Sets the protected $_url
            $url = $this->_cleanUrl($this->_url);
            $parameters = array();
            $controller = $this->_defaultController;
            $action = $this->_defaultAction;

            Event::fire("framework.router.initialize.before", array($url));
                            
            // If route is in predefined array
            $match = false;
            foreach ($this->_routes as $requestMethod) {

                foreach($requestMethod as $route) {

                    $matches = $route->matches($url);
                    $request = $this->_validateRequestMethod($route->method);

                    if ($matches && $request) {
                        $match = true;

                        $method = $route->method;
                        $controller = $route->controller;
                        $action = $route->action;
                        $parameters = $route->parameters;

                        Event::fire("framework.router.initialize.after", array($url, $method, $controller, $action, $parameters));
                        $this->_loadController($method, $controller, $action, $parameters);
                        return;
                    }
                }
            } 

            // No route has been predefined so no page is loaded
            if ($match == false) {
                throw new \Exception("Undefined Route", 404);
            }
        }
        
        
        /**
        * Fetches the GET from the url
        * @return array The $_GET array
        */
        private function _cleanUrl($url) {
            $url = rtrim($url, '/');
            return filter_var($url, FILTER_SANITIZE_URL);
        }
        

        /**
         * Verify that the called HTTP request method is
         * a valid value and correct for the defined route
         * @param  string $method HTTP Requst method as defined in route
         * @return boolean         
         */
        private function _validateRequestMethod($method = null) {

            if (in_array($this->_method, $this->_requestMethods) && $this->_method === $method) {
                return true;
            }

            return false;
        }


        /**
         * Load a controller
         * @return boolean|string
         */
        private function _loadController($method, $controller, $action,  $parameters = array()) {

            $name = ucfirst($controller);

            $this->_controller = $controller;
            $this->_action = $action;

            Event::fire("framework.router.controller.before", array($controller, $parameters));

            try {
                $instance = new $name(array(
                    "parameters" => $parameters
                ));
                Registry::set("controller", $instance);
            } catch (\Exception $e) {
                throw new \Exception("Controller {$name} not found", 404); 
            }

            Event::fire("framework.router.controller.after", array($controller, $parameters));
            if (!empty($action)) {

                // If request method valid append to action
                if ($this->_validateRequestMethod($method)) {
                        $action = strtolower($method).ucfirst($action);
                }

                // Check if called method exists
                if (!method_exists($instance, $action)) {
                    throw new \Exception("Action {$action} not found", 404);
                }
            
                // Check if method is callable (checking for @protected or @private)
                $inspector = new Inspector($instance);
                $methodMeta = $inspector->getMethodMeta($action);
            
                if (!empty($methodMeta["@protected"]) || !empty($methodMeta["@private"])) {
                    throw new \Exception("Action {$action} not found", 404);
                }
            }

            $hooks = function($meta, $type) use ($inspector, $instance) {
                if (isset($meta[$type])) {
                    $run = array();
                    foreach ($meta[$type] as $method) {
                        $hookMeta = $inspector->getMethodMeta($method);
                        if (in_array($method, $run) && !empty($hookMeta["@once"])) {
                            contiue;
                        }
                        $instance->$method();
                        $run[] = $method;
                    }
                }
            };

            Event::fire("framework.router.beforehooks.before", array($action, $parameters));

            $hooks($methodMeta, "@before");

            Event::fire("framework.router.beforehooks.after", array($action, $parameters));
            Event::fire("framework.router.action.before", array($action, $parameters));

            // Call Method or resort to default
            switch (true) {
                case (!empty($action)):
                    call_user_func_array(array(
                        $instance,
                        $action
                    ), is_array($parameters) ? $parameters : array());
                    break;
                default:
                    call_user_func_array(array(
                        $instance,
                        $this->_defaultAction
                    ), is_array($parameters) ? $parameters : array());
                    break;
                }

            Event::fire("framework.router.action.after", array($action, $parameters));
            Event::fire("framework.router.afterhooks.before", array($action, $parameters));

            $hooks($methodMeta, "@after");

            Event::fire("framework.router.afterhooks.after", array($action, $parameters));

            // unset controller        
            Registry::erase("controller");
        }

        public function _getExceptionForImplementation($method)
        {
            return new \Exception("{$method} method not implemented", 500);
        }
    }
}

