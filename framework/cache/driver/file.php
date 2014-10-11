<?php
namespace Framework\Cache\Driver {

	use Framework\Cache as Cache;
	use Framework\Configuration as Configuration;

	class File extends Cache\Driver {

		/**
		 * @readwrite
		 */
		protected $_duration = 3600;

		/**
		 * @readwrite
		 */
		protected $_cacheFolder = null;

		/**
		 * @readwrite
		 */
		protected $_cacheFile = null;

		/**
		 * @readwrite
		 */
		protected $_cacheExtension = '.cache';

		/**
		 * @readwrite
		 */
		protected $_autoDeleteExpired = true;

		public function __construct($options = array()) {

			parent::__construct($options);
			if (empty($options)) {
				$this->_cacheFolder = $paths('storage').'cache/';
				$this->_cacheFile = date("Y-m-d");
			}
		}

		/**
		 * Get cached object based upon key
		 * @param  string $key Key of cached object to be retrieved
		 * @return string      Value of cached object
		 */
		public function get($key) {

			// If desired clean up all expired cached objects
			if ($this->_autoDeleteExpired) {
				$this->deleteExpired();
			}

			$filename = $this->_getFileName();
			$cachedData = $this->_loadCache($filename);
			// If key exists unserialize and return
			if (!isset($cachedData[$key]['data'])) return null;
			return unserialize($cachedData[$key]['data']);

		}

		/**
		 * Get all ached objects
		 * @return [type] [description]
		 */
		public function getAll() {

			// If desired clean up all expired cached objects
			if ($this->_autoDeleteExpired) {
				$this->deleteExpired();
			}

			$res = array();
			$filename = $this->_getFileName();
			$cachedData = $this->_loadCache($filename);
			// If cached data exists unserialize and return
			if ($cachedData) {
				foreach ($cachedData as $key => $value) {
					$res[$key] = unserialize($value['data']);
				}
			}

			return $res;			
		}

		/**
		 * Store data in the cache
		 * @param string $key   Key to be used to store object
		 * @param string $value Value to be stored in cache
		 * @return object
		 */
		public function set($key, $value) {

			// Retrieve cache
			$filename = $this->_getFileName();
			$dataArray = $this->_loadCache($filename);

			// Build and add data to cache
			$data = array(
				'time' => time(),
				'data' => serialize($value)
			);

			if (is_array($dataArray)) {
				$dataArray[$key] = $data;
			} else {
				$dataArray = array($key => $data);
			}

			$cacheData = json_encode($dataArray);

			if (true !== file_put_contents($filename, $cacheData)) {
				return false;
			} 

			return $this;

		}

		/**
		 * Delete an object from the cache
		 * @param  string $key Key of object to be deleted
		 * @return object 
		 */
		public function delete($key) {

			// Retrieve cache
			$filename = $this->_getFileName();
			$cachedData = $this->_loadCache($filename);

			// Check for key and if present delete
			if (is_array($cachedData)) {
				if (isset($cachedData[$key])) {
					unset($cachedData[$key]);
					$cachedData = json_encode($cachedData);
					if (true !== file_put_contents($filename, $cachedData)) {
						return false;
					} 
				} else {
					throw new \Exception("Error: {$key} not found in cache", 500);				
				}
			}
			return $this;
		}

		/**
		 * Delete all objects in the cache
		 * @return object
		 */
		public function deleteAll() {

			// Retrieve cache
			$filename = $this->_getFileName();

			// if valid empty the cache
			if (file_exists($filename)) {
				if (true !== file_put_contents($filename, "")) {
					return false;
				}
			} else {
				throw new \Exception("Error: Cache not found", 500);
			}

			return $this;
		}


		/**
		 * Delete all expired objects in the cache
		 * @return object
		 */
		public function deleteExpired() {

			// Retrieve cache
			$filename = $this->_getFileName();
			$cachedData = $this->_loadCache($filename);

			// Filter out and remove all expired objects
			if (is_array($cachedData)) {
				$counter = 0;
				foreach ($cachedData as $key => $value) {
					$age = time() - $value['time'];
					if ($age > $this->_duration) {
						unset($cachedData[$key]);
						$counter++;
					}
				}
				if ($counter > 0) {
					$cachedData = json_encode($cacheData);
					if (true !== file_put_contents($filename, $cacheData)) {
						return false;
					} 
				}
				return $counter;
			}
		}

		/**
		 * Load the required cache
		 * @param  string $filename Cache to be loaded 
		 * @return string           Contents of the required cache
		 */
		protected function _loadCache($filename) {

			if (file_exists($filename)) {
				$file = file_get_contents($filename);
				return json_decode($file, true);
			}

			return false;
		}

		/**
		 * Get the full path and name the required cache
		 * @return string Full name of cache
		 */
		protected function _getFileName() {

			return 	$this->_cacheFolder.$this->_getHash($this->_cacheFile).$this->_cacheExtension;

		}

		/**
		 * Get the sha1 hash of a given string
		 * @param  string $string String to be hashed
		 * @return string         Hashed string
		 */
		protected function _getHash($string) {

			return sha1($string);
		}

	}
}