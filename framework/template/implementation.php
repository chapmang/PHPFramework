<?php
namespace Framework\Template {

	use Framework\Base as Base;
	use Framework\StringMethods as StringMethods;

	/**
	* Implementation
	* Base class allowing template implementations to be 
	* switched in and out
	* @version 1.0
	* @author Geoff Chapman <geoff.chapman@mac.com>
	* @package Frameework\Template
	*/
	class Implementation extends Base {
		
		/**
		 * Evaluate a source string to determine if it 
		 * matches a tag or a statement
		 * @param  string $source String from template to be tested
		 * @return array         
		 */
		public function match($source) {

			$type = null;
			$delimiter = null;

			foreach ($this->_map as $_delimiter => $_type) {
				if (!$delimiter || StringMethods::indexOf($source, $type['opener']) == -1) {
					$delimiter = $_delimiter;
					$type = $_type;
				}

				$indexOf = StringMethods::indexOf($source, $_type['opener']);

				if ($indexOf > -1) {
					if (StringMethods::indexOf($source, $type['opener']) > $indexOf) {
						$delimiter = $_delimiter;
						$type = $_type;
					}
				}
			}

			if ($type == null) {
				return null;
			}

			return array(
				'type' => $type,
				'delimiter' => $delimiter
			);
		}

		/**
		 * Using the _handler() method get the correct handler
		 * method and execute it
		 * @param  array $node     Array of template nodes
		 * @param  mixed $content  Content of a template node
		 * @return string          Results of the called handler method 
		 */
		public function handle($node, $content) {

			try {
				$handler = $this->_handler($node);
				return call_user_func_array(array($this, $handler), array($node, $content));
			} catch (Exception $e) {
				throw new \Exception("Handler Implementation error: " . $e->getMessage(), 500);
			}
		}

		/**
		 * Using an array of template nodes determine the correct
		 * handler method to execute
		 * @param  array $node Array of template nodes
		 * @return array       Grammar map element 
		 */
		protected function _handler($node) {

			// If node has no delimiter reject
			if (empty($node['delimiter'])) {
				return null;
			}

			// Return the correct grammar template element
			if (!empty($node['tag'])) {
				return $this->_map[$node['delimiter']]['tags'][$node['tag']]['handler'];
			}

			return $this->_map[$node['delimiter']]['handler'];
		}




	}
}