<?php
namespace Framework {

    use Framework\Base as Base;
    use Framework\Template as Template;
    use Framework\Event as Event;

    /**
     * View
     * Facade for standard template parsing
     * NB: No template pages will have to pass through
     * an overriding subclass
     * @version  1.0
     * @author Geoff Chapman <geoff.chapman@mac.com>
     * @package Framework
     */
    class View extends Base {
        
        /**
        * @readwrite
        */
        protected $_file;
        
        /**
        * @readwrite
        */
        protected $_data;
        
        /**
        * @read
        */
        protected $_template;

        /**
         * Create a template instance to allow parsing of templates
         * @param array $options 
         */
        public function __construct($options = array()) {

            parent::__construct($options);

            Event::fire("framework.view.construct.before", array($this->file));

            $this->_template = new Template(array(
                "implementation" => new Template\Implementation\Extended()
            ));

            Event::fire("framework.view.construct.after", array($this->file, $this->template));
            
        }

        /**
         * Render a given view using template implementation
         * and pass into it any required data
         * @return string The processed template
         */
        public function render() {

            Event::fire("framework.view.render.before", array($this->file));
            
            if (!file_exists($this->file)) {
                return "";
            }
            
            return $this
                ->template
                ->parse(file_get_contents($this->file))
                ->process($this->data);

        }

        /**
         * Retrieve key/value pair of data from template parser 
         * @param  string $key     Key of data element
         * @param  string $default Default value of data element
         * @return mixed          
         */
        public function get($key, $default = "") {

            if (isset($this->_data[$key])) {
                return $this->_data[$key];
            }
            return $default;
        }

        /**
         * Set key/value pair of data for template parser
         * @param mixed  $key  Key to be used for data element
         * @param mixed $value Value of data element
         */
        public function set($key, $value = null) {

            if (is_array($key)) {
                foreach ($key as $_key => $value) {
                    $this->_set($key, $value);
                }
                return $this;
            }

            $this->_set($key, $value);
            return $this;
        }

        /**
         * Remove data from the template
         * @param  mixed $key Key of data element to be deleted
         * @return void      
         */
        public function erase($key) {

            unset($this->_data[$key]);
            return $this;
        }

        /**
         * Make sure that key is suitable for use in setting 
         * a data element
         * @param mixed $key   Key to be used for data element
         * @param mixed $value Value to be used for data element
         */
        protected function _set($key, $value) {

            if (!is_string($key) && !is_numeric($key)) {
                throw new \Exception("Key must be a string or a number", 500);
            }

            $this->_data[$key] = $value;
        }

        public function _getExceptionArgument() {

            return new \Exception("Invalid argument", 500);
        }

        public function _getExceptionImplementation($method) {

            return new \Exception("{$method} method not implemented", 500);
        }

    }
}