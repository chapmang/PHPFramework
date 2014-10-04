<?php
namespace Framework {

	use Framework\Base as Base;
	use Framework\ArrayMethods as ArrayMethods;
	use Framework\StringMethods as StringMethods;

	class Template extends Base {

		/**
		 * @readwrite
		 */
		protected $_implementation;

		/**
		 * @readwrite
		 */
		protected $_header = "if (is_array(\$_data) && count(\$_data))
			extract(\$_data); \$_text = array();";

		/**
		 * @readwrite
		 */
		protected $_footer = "return implode(\$_text);";

		/**
		 * @read
		 */
		protected $_code;

		/**
		 * @read
		 */
		protected $_function;

		/**
		 * Create a function using the parsed template data
		 * @param  string $template Template to be parsed
		 * @return object           
		 */
		public function parse($template) { 

            if (!is_a($this->_implementation, "Framework\Template\Implementation")) {
                throw new Exception\Implementation();
            }
            
            $array = $this->_array($template);

            $tree = $this->_tree($array["all"]);
            
            $this->_code = $this->header.$this->_script($tree).$this->footer;
           
            $this->_function = create_function("\$_data", $this->code);
            
            return $this;
        }

		/**
		 * Execute a function generated from a parsed template
		 * Must be called after parse()
		 * @param  array  $data Data to be applied to a functioned template
		 * @return string       
		 */
		public function process($data = array()) {

			if ($this->function == null) {
				throw new \Exception("No valid templating function", 500);	
			}

			try {
				$function = $this->_function;
				// Resolve any residual slashes on plain text
				return stripslashes($function($data));
			} catch (Exception $e) {
				throw new \Exception($e, 500);
				
			}
		}

		/**
		 * If the template statement has specific argument format
		 * return as array the contents of the braces {...}
		 * @param  string $source     Chunk of template being parsed
		 * @param  string $expression Content of tags
		 * @return array
		 */
		protected function _arguments($source, $expression) {

            $args = $this->_array($expression, array(
                $expression => array(
                    "opener" => "{",
                    "closer" => "}"
                )
            ));
            
            $tags = $args["tags"];
            $arguments = array();
            $sanitized = StringMethods::sanitize($expression, "()[],.<>*$@");
            
            foreach ($tags as $i => $tag)  {
                $sanitized = str_replace($tag, "(.*)", $sanitized);
                $tags[$i] = str_replace(array("{", "}"), "", $tag);
            }
            
            if (preg_match("#{$sanitized}#", $source, $matches)) {
                foreach ($tags as $i => $tag) {
                    $arguments[$tag] = $matches[$i + 1];
                }
            }
            
            return $arguments;
        }

		/**
		 * Determine if the template chunk being parsed is a tag
		 * or plain string. Extract the content of the tag and generate
		 * a node array of metadata about the tag
		 * @param  string $source Chuck of template being parsed
		 * @return array 
		 */
		protected function _tag($source) {

			$tag = null;
			$arguments = array();

			// Is the source a tag or a statement?
			$match = $this->_implementation->match($source);

			if ($match == null) {
				return false;
			}

			$delimiter = $match['delimiter'];
			$type = $match['type'];

			// Break up the source and return metadata
			$start = strlen($type['opener']);
			$end = strpos($source, $type['closer']);
			$extract = substr($source, $start, $end - $start);

			if (isset($type['tags'])) {
				$tags = implode("|", array_keys($type['tags']));
				$regex = "#^(/){0,1}({$tags})\s*(.*)$#";

				if (!preg_match($regex, $extract, $matches))
                {
                    return false;
                }

				$tag = $matches[2];
                $extract = $matches[3];
                $closer = !!$matches[1];
			}

			// Is a basic tag
			if ($tag && $closer) {
				return array(
					'tag' => $tag,
					'delimiter' => $delimiter,
					'closer' => true,
					'source' => false,
					"arguments" => false,
					'isolated' => $type['tags'][$tag]['isolated']
				);
			}

			// Has additional arguments
			if (isset($type['arguments'])) {
				$arguments = $this->_arguments($extract, $type['arguments']);
			} else if ($tag && isset($type['tags'][$tag]['arguments'])) {
				$arguments = $this->_arguments($extract, $type['tags'][$tag]['arguments']);
			}

			// Is a statement with additional arguments
			return array(
				'tag' => $tag,
				'delimiter' => $delimiter,
				'closer' => false,
				'source' => $extract,
				'arguments' => $arguments,
				'isolated' => (!empty($type['tags']) ? $type['tags'][$tag]['isolated'] : false)
			);
		}

		/**
		 * De-construct a template string into tags and text using 
		 * Implementation\match()
		 * @param  string $source Template string to be broken up
		 * @return array
		 */
		protected function _array($source) {

			$parts = array();
			$tags = array();
			$all = array();

			$type = null;
			$delimiter = null;

			while ($source) {

				//Evaluate a source string to determine if it 
		 		// matches a tag or a statement
				$match = $this->_implementation->match($source);

				$type = $match['type'];
				$delimiter = $match['delimiter'];

				$opener = strpos($source, $type['opener']);
				$closer = strpos($source, $type['closer']) + strlen($type['closer']);

				// If there is an opener the string is a tag
				if ($opener !== false) {
					$parts[] = substr($source, 0, $opener);
					$tags[] = substr($source, $opener, $closer - $opener);
					$source = substr($source, $closer);
				} else {
					$parts[] = $source;
					$source = "";
				}
			}

			foreach ($parts as $i => $part) {
				$all[] = $part;
				if (isset($tags[$i])) {
					$all[] = $tags[$i];
				}
			}

			return array(
				'text' => ArrayMethods::clean($parts),
				'tags' => ArrayMethods::clean($tags),
				'all' => ArrayMethods::clean($all)
			);

		}


		/**
		 * Loop through template sections and organize them into a hierarchy
		 * and assign addition meta data to any tags
		 * @param  array $array Array of template segments
		 * @return array
		 */
		protected function _tree($array) {

			$root = array(
				'children' => array()
			);
			$current = &$root;

			foreach ($array as $i => $node) {

				$result = $this->_tag($node);
				
				if ($result) {
					
					if (isset($result['tag'])) {
						$tag = $result['tag'];
					} else {
						$tag = "";
					}

					if (isset($result['arguments'])) {
						$arguments = $result['arguments'];
					} else {
						$arguments = "";
					}

					if ($tag) {
						// If segment does not contain a closer
						if (!$result['closer']) {
							// clean up syntax if segment is isolated and 
							// preceded by an plain text segment
							$last = ArrayMethods::last($current['children']);
							if ($result['isolated'] && is_string($last)) {
								array_pop($current['children']);
							}

							$current['children'][] = array(
								'index' => $i,
								'parent' => &$current,
								'children' => array(),
								'raw' => $result['source'],
								'tag' => $tag,
								'arguments' => $arguments,
								'delimiter' => $result['delimiter'],
								'number' => count($current['children'])
							);
							$current = &$current['children'][count($current['children']) - 1];
						} else if (isset($current['tag']) && $result['tag'] == $current['tag']) {
							$start = $current['index'] + 1;
							$length = $i - $start;
							$current['source'] = implode(array_slice($array, $start, $length));
							$current = &$current['parent'];
						}
					} else {
						$current['children'][] = array(
							'index' => $i,
							'parent' => &$current,
							'children' => array(),
							'raw' => $result['source'],
							'tag' => $tag,
							'arguments' => $arguments,
							'delimiter' => $result['delimiter'],
							'number' => count($current['children'])
						);
					}
				} else {
					$current['children'][] = $node;
				}
			}
			return $root;
		}

		/**
		 * Walk through the hierarchy and either parse plain text or
		 * invoke the hander for valid tags thus generating a function body
		 * @param  array $tree Template hierarchy from _tree()
		 * @return string 
		 */
		protected function _script($tree) {
		
			$content = array();

			if (is_string($tree)) {
				$tree = addslashes($tree);
				return "\$_text[] = \"{$tree}\";";
			}

			if (count($tree['children'] > 0)) {
				foreach ($tree['children'] as $child) {
					$content[] = $this->_script($child);
				}
			}

			if (isset($tree['parent'])) {

				return $this->_implementation->handle($tree, implode($content));
			}

			return implode($content);
		}


		public function _getExceptionForImplementation($method) {

			return new \Exception("{$method} method no implemented", 500);
		}
	}
}