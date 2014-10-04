<?php
namespace Framework\Database\Connector {

	use Framework\Database as Database;
	use Framework\Configuration as Configuration;

	/**
	 * Oracle
	 * Class for connecting to and querying Oracle
	 * database vis OCI8
	 * Allowes both expressive query creation (see database/query),
	 * along with direct SQL queries
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 * @subpackage Database
	 */
	class Oracle {

		protected $_service;

		/**
		 * Driver
		 * @var  string 
		 * @readwrite
		 */
		protected $_driver = "oracle";

		/**
		 * Protocol
		 * @var string
		 * @readwrite
		 */
		protected $_protocol = 'TCP';

		/**
		 * Host server address
		 * @var string
		 * @readwrite
		 */
		protected $_host;

		/**
		 * Port for database connection
		 * @var string
		 * @readwrite
		 */
		protected $_port = "1521";

		/**
		 * Database name
		 * @var string
		 * @readwrite
		 */
		protected $_sid;
		
		/**
		 * Username for connection
		 * @var string
		 * @readwrite
		 */
		protected $_username;

		/**
		 * Password for connection
		 * @var string
		 * @readwrite
		 */
		protected $_password;

		/**
		 * Character set for data
		 * @var string
		 * @readwrite
		 */
		protected $_charset = "AL32UTF8";

		/**
		 * Prefix for names
		 * @var string 
		 * @readwrite
		 */
		protected $_prefix = '';

		/**
		 * Status of connection
		 * @var boolean
		 * @readwrite
		 */
		protected $_isConnected = false;


		/**
		 * Test for a valid PDO service
		 * @return boolean Status of service
		 */
		public function _isValidService() {
			$isEmpty = empty($this->_service);
			$isInstance = $this->_service instanceof \PDO;
			if ($this->_isConnected && $isInstance && !$isEmpty) {
				return true;
			}
			return false;
		}

		/**
		 * Connect to database via OCI8 using supplied details
		 * @return object Connection to Oracle database
		 */
		public function connect() {
				try {
					$cds = "(DESCRIPTION =
								(ADDRESS_LIST = 
									(ADDRESS = 
										(PROTOCOL = {$this->_protocol})
										(HOST = {$this->_host})
										(PORT = {$this->_port})))
								(CONNECT_DATA = (SID = {$this->_sid})))";

					$this->_service = oci_connect($this->_username, $this->_password, $this->_cds, $this->_charset);
				} catch (Exception $e) {
					throw new \Exception(oci_error(), 500);	
				}
			$this->_isConnected = true;
			return $this->_service;
		}

		/**
		 * Disconnect from database
		 */
		public function disconnect() {
			if($this->_isValidService()) {
				$this->isConnected = false;
				$this->_service = null;
			}
			return $this;
		}

		/**
		 * Return a corresponding query instance
		 */
		public function query() {
			return new Database\Query\Oracle(array(
				"connector" => $this
			));
		}

		/**
		 * Prepare and Execute the provided SQL statement
		 * @param  string $sql SQL statement to be executed
		 * @return mixed
		 */
		public function execute($sql, $bindings = array(), $insert = false){
			// Is an Insert statement add a return into clause
			if ($insert === true) $sql = $sql . " return id into :id";
			try {
				$statement = oci_parse($conn, $sql);
				// Bind any parameters to the query string
				if (!empty($bindings)) {
					foreach ($bindings as $key => $value) {
						oci_bind_by_name($stm, "$key", $bindings[$key]);
					}
				}
				// If an Insert statement bind to the return into clause
				if ($insert === true) {
					oci_bind_by_name($stm, ":id", $id, 20, SQLT_INT);
				}
				$result = oci_execute($stm);
			} catch (Exception $e) {
				throw new \Exception("Error running PDO Execute", 500);	
			}
			if ($insert === true ) {
				return $id;
			} else {
				return array($statement, $result);
			}
		}

		/**
		 * Fetch results from OCI statement object
		 * @param  object $statement  Prepared OCI statement object
		 * @param  string $fetchStyle Style of returned row/s
		 * @return mixed             Returned row/s
		 */
		public function fetch ($statement, $fetchStyle) {
			switch ($fetchStyle) {
				case 'fetch_all':
					return oci_fetch_all($statement);
					break;
				case 'fetch_array':
					return oci_fetch_array($statement);
					break;
				case 'fetch_assoc':
					return oci_fetch_assoc($statement);
					break;
				case 'fetch':
					return oci_fetch_object($statement);
					break;
				default:
					return oci_fetch_array($statement, OCI_BOTH);
					break;
			}
		}

		/**
		 * Run a select statement and return a single result.
		 * @param  string $query    Query to run
		 * @param  array  $bindings Optional parameters to be bound to query
		 * @return mixed           Returned row
		 */
		public function selectOne($query, $bindings = array()) {
			$records = $this->select($query, $bindings);

			return count($records) > 0 ? reset($records) : null;
		}

		/**
		 * Run a select statement against the database.
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return array
		 */
		public function select($query, $bindings = array()) {
			list($statement, $result) = $this->execute($query, $bindings);
			if (!$result) {
                $error = oci_error();
                throw new \Exception("There was an error with your SQL query: {$error}");
            }

            $fetchStyle = Configuration::get('database.fetch');
			return $this->fetch($statement, $fetchStyle);
			}

		/**
		 * Run an insert statement against the database.
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return bool
		 */
		public function insert($query, $bindings = array(), $insert = true) {
			return $this->execute($query, $bindings, $insert);
		}

		/**
		 * Run an update statement against the database.
		 *
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return int
		 */
		public function update($query, $bindings = array()) {
			list($statement, $result) = $this->execute($query, $bindings);
			if ($result) {
				return $this->getAffectedRows($statement);
			}
		}

		/**
		 * Run a delete statement against the database.
		 *
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return int
		 */
		public function delete($query, $bindings = array()) {
			list($statement, $result) = $this->execute($query, $bindings);
			if ($result) {
				return $this->getAffectedRows($statement);
			}
		}

		/**
		 * Escape a string for usage in a query.
		 * This uses the correct quoting mechanism for the default database connection.
		 * NB: Strongly recommend that use 'Prepaired' statements
	 	 * @param  string $value String to be quoted
		 * @return string        Quoted string
		 */
		public function escape($value) {
			return $this->_service->quote($value);
		}

		/**
		 * Returns the number of rows affected by the last prepared statement
		 * DELETE, INSERT, or UPDATE (SELECT Statements should not be trusted)
		 * @return int The number of rows affected
		 */
		public function getAffectedRows($statement) {
			return oci_num_rows($statement);
		}

		/**
		 * Returns an array of error information about the last 
		 * operation performed by this statement handle
		 * @return array Information about last error
		 */
		public function getLastError() {
			return $this->_service->errorInfo();
		}
	}
}