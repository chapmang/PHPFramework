<?php
namespace Framework\Database\Connector {

	use PDO;

	abstract class Connector {


		/**
		 * The PDO connection options.
		 * @var array
		 */
		protected $_options = array(
				// PDO::ATTR_CASE => PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
				PDO::ATTR_STRINGIFY_FETCHES => false,
				PDO::ATTR_EMULATE_PREPARES => false,
		);

		/**
		 * Establish a PDO connection
		 * @param  array $config 
		 * @return PDO
		 */
		abstract public function connect($config);

		/**
		 * Get the PDO connection options for the configuration.
		 * Developer specified options will override the default connection options.
		 * @param  array  $config
		 * @return array
		 */
		protected function _options($config) {

			$options = (isset($config['options'])) ? $config['options'] : array();

			return $options + $this->_options;
		}
	}
}