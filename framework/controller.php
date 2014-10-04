<?php

namespace Framework
{
    use Framework\Base as Base;
    use Framework\View as View;
    use Framework\Event as Event;
    use Framework\Registry as Registry;
    use Framework\Template as Template;
    
    class Controller extends Base
    {
        /**
        * @read
        */
        protected $_name;
        
        /**
        * @readwrite
        */
        protected $_parameters;
        
        /**
        * @readwrite
        */
        protected $_layoutView;
        
        /**
        * @readwrite
        */
        protected $_actionView;
        
        /**
        * @readwrite
        */
        protected $_willRenderLayoutView = true;
        
        /**
        * @readwrite
        */
        protected $_willRenderActionView = true;
        
        /**
        * @readwrite
        */
        protected $_defaultPath = "views";
        
        /**
        * @readwrite
        */
        protected $_defaultLayout = "layouts/standard";
        
        /**
        * @readwrite
        */
        protected $_defaultExtension = "html";
        
        /**
        * @readwrite
        */
        protected $_defaultContentType = "text/html; charset=utf-8";
        
        /**
         * [__construct description]
         * @param array $options [description]
         */
        public function __construct($options = array()) {

            parent::__construct($options);
            
            Event::fire("framework.controller.construct.before", array($this->name));
            
            // Get layout location and pass to View instance
            if ($this->willRenderLayoutView) {        
                $defaultPath = $this->defaultPath;
                $defaultLayout = $this->defaultLayout;
                $defaultExtension = $this->defaultExtension;
                
                $view = new View(array(
                    "file" => path('app') . "{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
                ));

                $this->setLayoutView($view);

            }
            
            if ($this->willRenderActionView) {  
                $router = Registry::get("router");
                $controller = $router->controller;
                $action = $router->getAction();
                
                $view = new View(array(
                    "file" => path('app') ."{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
                ));
                
                $this->setActionView($view);
            }
            
            Event::fire("framework.controller.construct.after", array($this->name));
        }
        
        /**
         * Render view, making sure all layout and action views are
         * includded in the correct place
         * @return void 
         */
        public function render() {
            Event::fire("framework.controller.render.before", array($this->name));
            
            $defaultContentType = $this->getDefaultContentType();
            $results = null;
            
            $doAction = $this->willRenderActionView && $this->actionView;
            $doLayout = $this->willRenderLayoutView && $this->layoutView;
            try {

                // Render the action view
                if ($doAction) {
                    $view = $this->actionView;
                    $results = $view->render();

                    $this->actionView
                         ->template
                         ->implementation
                         ->set("action", $results);
                }
                
                // Render the layout view
                if ($doLayout) {
                    $view = $this->layoutView;
                    $view->set("template", $results);
                    $results = $view->render();
                    
                    header("Content-type: {$defaultContentType}");
                    echo $results;

                } else if ($doAction) {
                    
                    header("Content-type: {$defaultContentType}");
                    echo $results;
                }
                
                // Prevent re-rendering
                $this->willRenderLayoutView = false;
                $this->willRenderActionView = false;

            } catch (Exception $e) {
                throw new \Exception("Invalid layout/template syntax: ". $e->getMessage(), 500);
            }
            
            Event::fire("framework.controller.render.after", array($this->name));
        }
        
        /**
         * Call the render method at the end of each action's execution
         */
        public function __destruct() {
            Event::fire("framework.done", array("render"));
            Event::fire("framework.controller.destruct.before", array($this->name));
            
            $this->render();
            
            Event::fire("framework.controller.destruct.after", array($this->name));
        }

        protected function getName() {

            if (empty($this->_name)) {
                $this->_name = get_class($this);
            }
            return $this->_name;
        }
        
        protected function _getExceptionForImplementation($method)
        {
            return new \Exception("{$method} method not implemented");
        }
        
        
    }
}