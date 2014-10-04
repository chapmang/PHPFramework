<?php
namespace Framework\Logs\Writer {

	use Framework\Logs as Logs;

	/**
	 * HTML
	 * Write logs to files to html
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 * @subpackage Logs
	 */
	class Html extends Logs\Writer {
    protected $_file;
    protected $_entries;
    protected $_start;
    protected $_end;

    	public function __construct() {
    		
			$this->_start = microtime();
			$this->_entries = array();
    	}

		/**
		 * Write event logs to file
		 * @param  string $message Descriptive message to be written to log file
		 */
		public function logEvent ($message = null) {
			$this->_file = APP_PATH . "/logs/events/" . date("Y-m-d") . ".html";
			$time = microtime(true);
			$micro = sprintf("%06d",($time - floor($time)) * 1000000);
			$date = new \DateTime( date('Y-m-d H:i:s.'.$micro,$time) );
			$this->_entries[] = array(
				"message" => "<strong>[" . $date->format("Y-m-d H:i:s.u") . "] </strong>" . $message,
				"time" => microtime()
			);		
		}

		public function logQuery ($sql) {
			$this->_file = APP_PATH . "/logs/queries/" . date("Y-m-d") . ".html";
			$time = microtime(true);
			$micro = sprintf("%06d",($time - floor($time)) * 1000000);
			$date = new \DateTime( date('Y-m-d H:i:s.'.$micro,$time) );
			$this->_entries[] = array(
				"message" => "<strong>[" . $date->format("Y-m-d H:i:s.u") . "] </strong>" . $sql,
				"time" => microtime()
			);
		}

		public function __destruct() {
			$messages = "";
	        $last = $this->_start;
	        $times = array();
	        
	        foreach ($this->_entries as $entry) {
	            $messages .= $entry["message"] . "<br/>";
	            $times[] = $entry["time"] - $last;
	            $last = $entry["time"];
	        }
	        
	        $messages .= "Average: " . $this->_average($times);
	        $messages .= ", Longest: " . max($times);
	        $messages .= ", Shortest: " . min($times);
	        $messages .= ", Total: " . (microtime() - $this->_start);
	        $messages .= "<br/><hr/>";

			file_put_contents($this->_file, $messages, FILE_APPEND);
		}
	}
}