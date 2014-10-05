<?php
namespace Framework {

	use Framework\Base as Base;
	use Framework\Cache as Cache;
	use Framework\Events as Events;
	use Framework\Registry as Registry;
	/**
	* 
	*/
	class Cache extends Base {
		
		protected $_driver;

		protected $_options;

		protected function initialize() {

			Event::fire("framework.cache.initialize.before", array($this->_driver, $this->_options));

			if (!$this->_driver) {
				$config = Configuration::get("cache");

				$this->driver = $config['default'];
				$this->options = Configuration::get("cache.settings." . $this->_driver);
			}

			if (!$this->_driver) {
				throw new \Exception("Invalid type", 500);
			}

			Event::fire("framework.cache,.initializ.after",  array($this->_driver, $this->_options));

			switch ($this->_driver) {

				case "file":
					return new Cache\Driver\File($this->_options);
					break;
				case "memcached":
					return new Cache\Driver\Memcached($this->_options);
					break;
				case "wincache":
					return new Cache\Driver\Wincache($this->_options);
					break;
				default:
					throw new Exception("Ivalid type", 500);
			}
		}

	}
}