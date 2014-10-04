<?php 
namespace Framework\Template\Implementation {

	use Framework\Template as Template;
	use Framework\StringMethods as StringMethods;

	/**
	 * Standard
	 * Standard template implementation including grammar
	 * and handlers for functionality described by the grammar
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework\Temaplate\Implementation 
	 */
	class Standard extends Template\Implementation {

		/**
		 * Grammar map for standard language tags in
		 * template dialect
		 * @var array
		 */
		protected $_map = array(
            "echo" => array(
                "opener" => "{echo",
                "closer" => "}",
                "handler" => "_echo"
            ),
            "script" => array(
                "opener" => "{script",
                "closer" => "}",
                "handler" => "_script"
            ),
            "statement" => array(
                "opener" => "{",
                "closer" => "}",
                "tags" => array(
                    "foreach" => array(
                        "isolated" => false,
                        "arguments" => "{element} in {object}",
                        "handler" => "_each"
                    ),
                    "for" => array(
                        "isolated" => false,
                        "arguments" => "{element} in {object}",
                        "handler" => "_for"
                    ),
                    "if" => array(
                        "isolated" => false,
                        "arguments" => null,
                        "handler" => "_if"
                    ),
                    "elseif" => array(
                        "isolated" => true,
                        "arguments" => null,
                        "handler" => "_elif"
                    ),
                    "else" => array(
                        "isolated" => true,
                        "arguments" => null,
                        "handler" => "_else"
                    ),
                    "macro" => array(
                        "isolated" => false,
                        "arguments" => "{name}({args})",
                        "handler" => "_macro"
                    ),
                    "literal" => array(
                        "isolated" => false,
                        "arguments" => null,
                        "handler" => "_literal"
                    )
                )
            )
        );
		
		/**
		 * Optimize echo string from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string          
		 */
		protected function _echo($tree, $content) {

			$raw = $this->_script($tree, $content);
			return "\$_text[] = {$raw}";
		}

		/**
		 * Optimize a script tag from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _script($tree, $content) {

			if (!empty($tree['raw'])) {
				$raw = $tree['raw'];
			} else {
				$raw = "";
			}
			return "{$raw};";
		}

		/**
		 * Optimize an each tag from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _each($tree, $content) {

			$object = $tree['arguments']['object'];
			$element = $tree['arguments']['element'];

			return $this->_loop(
				$tree,
				"foreach ({$object} as {$element}_i => {$element}) {
                    {$content}
                }"
			);
		}

		/**
		 * Optimize a for tag from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _for($tree, $content) {

			$object = $tree['arguments']['object'];
			$element = $tree['arguments']['element'];

			return $this->_loop(
				$tree,
				"for ({$element}_i = 0; {$element}_i < count({$object}); {$element}_i++) {
                    {$element} = {$object}[{$element}_i];
                    {$content}
                }"
			);
		}

		/**
		 * Optimize an if tag from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _if($tree, $content) {

			$raw = $tree['raw'];
			return "if ({$raw}) {{$content}}";
		}

		/**
		 * Optimize an else if tag from a template for final function
		 * @param  array $tree    Node from the template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _elif($tree, $content) {

			$raw = $tree['raw'];
			return "elseif ({$raw}) {{$content}}"; 
		}

		/**
		 * Optimize an else tag from a template for final function
		 * @param  array $tree    Node from template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _else ($tree, $content) {

			return "else {{$content}}";
		}

		/**
		 * Optimize a macro (function) tag from a template for final function
		 * @param  array $tree    Node from template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _macro($tree, $content) {

			$arguments = $tree['arguments'];
			$name = $arguments['name'];
			$args = $arguments['args'];

			return "function {$name}({$args}) {
                \$_text = array();
                {$content}
                return implode(\$_text);
            }";
		}

		/**
		 * Optimize a literal tag from a template for final function
		 * @param  array $tree     Node from template tree
		 * @param  mixed $content Content of node
		 * @return string
		 */
		protected function _literal($tree, $content) {

			$source = addslashes($tree['source']);
			return "\$_text[] = \"{$source}\";";
		}

		/**
		 * Loop helper for checking contents of a looping method
		 * as long as they have an else tag following them
		 * @param  array $tree  Node from template tree
		 * @param  mixed $inner Content of node
		 * @return mixed
		 */
		protected function _loop($tree, $inner) {

			$number = $tree['number'];
			$object = $tree['arguments']['object'];
			$children = $tree['parent']['children'];

			if (!empty($children[$number + 1]['tag']) && $children[$number + 1]['tag'] == "else") {
				return "if (is_array({$object}) && count({$object}) > 0) {{$inner}}";
			}

			return $inner;
		}
	}
}