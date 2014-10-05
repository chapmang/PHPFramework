<?php
namespace Framework\Cache\Driver {

	use Framework\Cache as Cache;

	class Memecached extends Cache\Driver {

		protected $_duration = 3600;

		protected $_service;

		protected $_host = "127.0.0.1";

		protected $_port = "11211";

		protected $_isConnected = false;

		protected function _isValidService() {

			$isEmpty = empty($this->_service);
			$isInstance = $this->service instanceof \Memcache;

			if ($this->isConnected && $isInstance && !$isEmpty) {

				return true;
			}

			return false;
		}

		public function connect() {

			try {
				$this->_service = new \Memcache();
				$this->_service->connect($this->host, $this->port);
				$this->isConnected = true;
			} catch (\Exception $e) {
				throw new Exception("Uncable to connect to Memcache service", 500);				
			}
			return $this;
		}

		public function disconnect() {

			if ($this->_isValidService()) {
				$this->_service->close();
				$this->isConnected = false;
			}

			return $this;
		}

		public function get($key, $default = NULL) {

			if(!$this->_isValidService()) {
				throw new \Exception("Not a connected to a valid cahce serveice", 500);				
			}

			$value = $this->_service->get($key, MEMCACHE_COMPRESSED);

			if ($value) {
				return $value;
			}

			return $default;
		}

		public function set($key, $value) {

			if (!$this->_isValidService()) {
				throw new Exception("Not a connected to a valid cahce serveice", 500);	
			}

			$this->_service->set($key, $value, MEMCACHE_COMPRESSED, $this->_duration);
			return $this;
		}

		public function isCached($key) {

			 if ($this->get($key)) {
			 	return true;
			 }

			 return false;
		}

		public function delete($key) {

			if ($this->_isValidService()) {
				throw new Exception("Not a connected to a valid cahce serveice", 500);				
			}

			$this->_service->delete($key);
			return $this;
		}
	}
}