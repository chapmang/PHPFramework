<?php
namespace Framework\Template\Implementation {

    use Framework\Request as Request;
    use Framework\Registry as Registry;
    use Framework\Template as Template;
    use Framework\StringMethods as StringMethods;
    use Framework\RequestMethods as RequestMethods;
    
    class Extended extends Standard {
        /**
        * @readwrite
        */
        protected $_defaultPath = "views";
        
        /**
        * @readwrite
        */
        protected $_defaultKey = "_data";
        
        /**
        * @readwrite
        */
        protected $_index = 0;
        
        /**
         * Append Extended grammar to the standard language map
         * @param array $options 
         */
        public function __construct($options = array()) {

            parent::__construct($options);
            
            $this->_map = array(
                "partial" => array(
                    "opener" => "{partial",
                    "closer" => "}",
                    "handler" => "_partial"
                ),
                "include" => array(
                    "opener" => "{include",
                    "closer" => "}",
                    "handler" => "_include"
                ),
                "yield" => array(
                    "opener" => "{yield",
                    "closer" => "}",
                    "handler" => "yield"
                )
            ) + $this->_map;
            
            $this->_map["statement"]["tags"] = array(
                "set" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "set"
                ),
                "append" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "append"
                ),
                "prepend" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "prepend"
                )
            ) + $this->_map["statement"]["tags"];
        }
        
        /**
         * Fetch a sub-template and place within the main template
         * Both templates to be proceed at the same time  allowing
         * for the sharing of data
         * @param  array $tree    Node from the template tree
         * @param  mixed $content Content of node
         * @return string         Executable sub-template code 
         */
        protected function _include($tree, $content) {

            // New template instance allowing for the nesting 
            // of templates, e.g., sub-templates can have sub-templates
            $template = new Template(array(
                "implementation" => new self()
            ));
            
            // Retrieve the file to include an parse its contents
            $file = trim($tree["raw"]);
            $path = $this->defaultPath;
            $content = file_get_contents(path('app')."{$path}/{$file}");
            
            $template->parse($content);
            $index = $this->_index++;
            
            // Return the function generated by the template
            return "\$_anon = function(\$_data){
                ".$template->code."
            };\$_text[] = \$_anon(\$_data);";
        }
        
        /**
         * Make GET request to given URL and place the results
         * in the template file
         * @param  array $tree    Node from the template tree
         * @param  mixed $content Content of node
         * @return string         String to be included in template
         */
        protected function _partial($tree, $content) {

            $address = trim($tree["raw"], " /");
            
            // Convert a relative URl to an absolute URL
            if (StringMethods::indexOf($address, "http") != 0) {
                $host = RequestMethods::server("HTTP_HOST");
                $address = "http://{$host}/{$address}";
            }
            
            // Make GET request to URL and return results to 
            // template $_text array 
            $request = new Request();
            $response = addslashes(trim($request->get($address)));
            
            return "\$_text[] = \"{$response}\";";
        }

        /**
         * Optimize set string from a template for final function
         * Store data for sharing by any parsed templates
         * @param array $key    Node from the template tree
         * @param mixed $value  Value to be stored
         */
        public function set($key, $value) {
            
            if (StringMethods::indexOf($value, "\$_text") > -1) {
                $first = StringMethods::indexOf($value, "\"");
                $last = StringMethods::lastIndexOf($value, "\"");
                $value = stripslashes(substr($value, $first + 1, ($last - $first) - 1));
            }
            
            if (is_array($key)) {
                $key = $this->_getKey($key);
            }
            
            $this->_setValue($key, $value);
        }
        
        /**
         * Optimize an append string from a template for final function
         * Append given data to that stored in the same key
         * @param  string $key   Key to store data under
         * @param  mixed $value  Data to be stored
         * @return void
         */
        public function append($key, $value) {
           
            if (is_array($key)) {
                $key = $this->_getKey($key);
            }
            
            $previous = $this->_getValue($key);
            $this->set($key, $previous.$value);
        }
        
        /**
         * Optimize a prepend string from a template for final function
         * Prepend given data to that stored in the same key
         * @param  string $key   Key to store data under
         * @param  mixed $value  Data to be stored
         * @return void 
         */
        public function prepend($key, $value) {
            
            if (is_array($key)) {
                $key = $this->_getKey($key);
            }
            
            $previous = $this->_getValue($key);
            $this->set($key, $value.$previous);
        }
        

        /**
         * Optimize a yield string from a template for final function
         * Return any stored data for a given key
         * @param  array $tree    Node form template tree
         * @param  mixed $content Content of node
         * @return string
         */
        public function yield($tree, $content) {

            $key = trim($tree["raw"]);
            $value = addslashes($this->_getValue($key));
            
            return "\$_text[] = \"{$value}\";";
        }

        /**
         * Extract a given storage key from the construct
         * @param  array $tree Node from the template tree
         * @return string      Key as a string
         */
        protected function _getKey($tree) {

            if (empty($tree["arguments"]["key"])) {
                return null;
            }
            
            return trim($tree["arguments"]["key"]);
        }
        
        /**
         * Use global Registry to store given $key/$value pair 
         * @param string $key  Key to store value under
         * @param mixed $value Value to be stored
         */
        protected function _setValue($key, $value) {

            if (!empty($key)) {
                $data = Registry::get($this->defaultKey, array());
                $data[$key] = $value;
                
                Registry::set($this->defaultKey, $data);
            }
        }
        
        /**
         * Fetch a given $key/$value pair from the global Registry
         * @param  string $key Key  to be fetched
         * @return mixed       Value fetched
         */
        protected function _getValue($key) {
            
            $data = Registry::get($this->defaultKey);
            
            if (isset($data[$key])) {
                return $data[$key];
            }
            
            return "";
        }
    }
}