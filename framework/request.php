<?php
namespace Framework {

	use Framework\Base as Base;
	use Framework\StringMethods as StringMethods;
	use Framework\RequestMethods as RequestMethods;

	/**
	 * Request
	 * Using cURUL make a HTTP request and return the results
	 * via the Request\Response class
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 */
	class Request extends Base {

		protected $_request;

		/**
		 * @readwrite
		 */
		protected $_willFollow = true;

		/**
		 * @readwrite
		 */
		protected $_headers = array();

		/**
		 * @readwrite
		 */
		protected $_options = array();

		/**
		 * @readwrite
		 */
		protected $_referer;

		/**
		 * @readwrite
		 */
		protected $_agent;

		public function __construct($options = array()) {

			parent::__construct($options);
			$this->setAgent(RequestMethods::server("HTTP_USER_AGENT", "Curl/PHP ".PHP_VERSION));
		}

		/**
		 * Process a DELETE HTTP request
		 * @param  string $url        Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return HTTP_REQUEST       
		 */
		public function delete($url, $parameters = array()) {

			return $this->request("DELETE", $url, $parameters);
		}

		/**
		 * Process a GET HTTP request, turning any given parameters
		 * into a valid query string
		 * @param  string $url        Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return HTTP_REQUEST       
		 */
		public function get($url, $parameters = array()) {

			if (!empty($parameters)) {
				$url .= StringMethods::indexOf($url, "?") ? "&" : "?";
				if (is_string($parameters)) {
					$url .= $parameters;
				} else {
					$url .= http_build_query($parameters, "", "&");
				}
			}

			return $this->request("GET", $url);
		}

		/**
		 * Process a HEAD HTTP request
		 * @param  string $url        Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return HTTP_REQUEST
		 */
		public function head($url, $parameters = array()) {

			return $this->request("HEAD", $url, $parameters);
		}

		/**
		 * Process a POST HTTP request
		 * @param  string $url        Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return HTTP_REQUEST       
		 */
		public function post($url, $parameters = array()) {

			return $this->request("POST", $url, $parameters);
		}

		/**
		 * Process a PUT HTTP request
		 * @param  string $url       Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return HTTP_REQUEST     
		 */
		public function put($url, $parameters = array()) {

			return $this->request("PUT", $url, $parameters);
		}

		/**
		 * Using cURL make a HTTP request
		 * @param  string $method     Request method to be used
		 * @param  string $url        Target remote url
		 * @param  array  $parameters Parameters to be sent to url
		 * @return mixed              Response of HTTP request
		 */
		public function request($method, $url, $parameters = array()) {

			Event::fire("framework.request.request.before", array($method, $url, $parameters));

			// Create a new cURL resource instance
			$request = $this->_request = curl_init();

			// Turn parameters into a query string
			if (is_array($parameters)) {
				$parameters = http_build_query($parameters, "", "&");
			}

			// Set the cURL instance parameters and make the request
			$this->_setRequestMethod($method)
				 ->_setRequestOptions($url, $parameters)
				 ->_setRequestHeaders();
			$response = curl_exc($request);

			if ($response) {
				$response = new Request\Response(array(
					'response' => $response
				));
			} else {
				throw new \Exception(curl_errno($request) . ' - ' . curl_error($request), 500);				
			}

			Event::fire("framework.request.request.after", array($method, $url, $parameters));

			curl_close($request);
			return $response;
		}

		/**
		 * Set cURL parameters for each of the different request methods
		 * @param string $method HTTP request method being used
		 */
		protected function _setRequestMethod($method) {

			switch (strtoupper($method)) {
				case "HEAD":
					$this->_setOption(CURLOPT_NOBODY, true);
					break;
				case "GET":
					$this->_setOption(CURLOPT_HTTPGET, true);
					break;
				case "POST":
					$this->_setOption(CURLOPT_POST, true);
					break;
				default:
					$this->_setOption(CURLOPT_CUSTOMEREQUEST, $method);
					break;
			}

			return $this;
		}

		/**
		 * Iterate through all the request specific parameters
		 * that need to be set, inc. the URL, User Agent, redirects etc
		 * @param string $url       Target remote url
		 * @param array $parameters Parameters to be sent to url
		 */
		protected function _setRequestOptions($url, $parameters) {

			$this->_setOption(CURLOPT_URL, $url)
				 ->_setOption(CURLOPT_HEADER, true)
				 ->_setOption(CURLOPT_RETURNTRANSFER, true)
				 ->_setOption(CURLOPT_USERAGENT, $this->getAgent());

			if (!empty($parameters)) {
				$this->_setOption(CURLOPT_POSTFIELDS, $parameters);
			}
			
			if ($this->_willFollow){
				$this->_setOption(CURLOPT_FOLLOWLOCATION, true);
			}

			if ($this->_referer) {
				$this->_setOption(CURLOPT_REFERER, $this->referer);
			}

			foreach ($this->_options as $key => $value) {
				$this->_setOption(constant($this->_normalize($key)), $value);
			}

			return $this;
		}

		/**
		 * Iterate through the specified headers and ad any custom
		 * headers to the HTTP request
		 */
		protected function _setRequestHeaders() {

			$headers = array();
			foreach ($this->_headers as $key => $value) {
				$headers[] = $key . ': ' . $value;
			}
			$this->_setOption(CURLOPT_HTTPHEADER, $headers);
			return $this;
		}

		/**
		 * Set cURL options based on key/value pairs
		 * @param string $key   cURL option to be set
		 * @param string $value Value to set to cURL option
		 */
		protected function _setOption($key, $value) {
			
			curl_setopt($this->_request, $key, $value);
			return $this;
		}

		/**
		 * Format a cURL option name to work with PHP cURL class
		 * @param  string $key Option name to be normalized
		 * @return string      
		 */
		protected function _normalize($key) {

			return "CURLOPT_" . str_replace("CURLOPT_", "", strtoupper($key));
		}


		protected function _getExceptionForImplementation($method) {

			return new \Exception("{$method} not implemented", 500);
		}

		protected function _getExceptionForArgument() {

			return new \Exception("Invalid argument");
		}
	}
}