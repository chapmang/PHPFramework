<?php
namespace Framework\Database {

	use PDO;
	use Framework\Base as Base;
	use Framework\Configuration as Configuration;
	use Closure;
	use Framework\Database\Query;

	class Connection extends Base {

		/**
		 * [$_pdo description]
		 * @var [type]
		 * @readwrite
		 */
		protected $_pdo;

		/**
		 * @readwrite
		 */
		protected $_type;

		/**
		 * @readwrite
		 */
		protected $_query;

		public function __construct(PDO $pdo, $type, $options = array()) {

			parent::__construct($options);
			$this->_type = $type;
			$this->_pdo = $pdo;
		}

		/**
		 * Return a corresponding query instance
		 */
		public function query() {
			 switch ($this->type) {
                case "mysql":
                	return $this->query = new Query\Mysql(array(
						"connector" => $this
					));
                    break;
                case "sqlsrv":
                    return $this->query = new Query\Sqlsrv(array(
						"connector" => $this
					));
                    break;
                case "oracle":
                    return $htis->query = new Query\Oracle(array(
						"connector" => $this
					));
                    break;
                default:
                    throw new \Exception("Database driver [{$this->type}] is not supported.", 500);
                    break;
			}
		}

		/**
		 * Prepare and Execute the provided SQL statement
		 * @param  string $sql SQL statement to be executed
		 * @return mixed
		 */
		public function execute($sql, $bindings = array()){

			// // Convert all DateTime binding into to the compatable
			// // date-time string for the databse in question
			$datetime = $this->query->datetime;

			foreach ($bindings as $key => $value) {
				if ($value instanceof \DateTime) {
					$bindings[$key] = $value->format($datetime); 
				}
			}

			try {
				$statement = $this->pdo->prepare($sql);
				$result = $statement->execute($bindings);
			} catch (Exception $e) {

				throw new \Exception("PDO Execute Error: ". $e->getMessage(), 500);	
			}
			return array($statement, $result);
		}

		/**
		 * Fetch results from PDO statement object
		 * @param  object $statement  Prepared PDO statement object
		 * @param  string $fetchStyle Style of returned row(s)
		 * @return mixed              Returned row(s)
		 */
		public function fetch ($statement, $fetchStyle) {
            if ($fetchStyle === PDO::FETCH_CLASS) {
                return $statement->fetchAll(PDO::FETCH_CLASS, 'stdClass');
            } else {
                return $statement->fetchAll($fetchStyle);
            }
		}

		/**
		 * Run a select statement and return a single result.
		 * @param  string $query    Query to run
		 * @param  array  $bindings Optional parameters to be bound to query
		 * @return mixed            Returned row
		 */
		public function selectOne($query, $bindings = array()) {
			$records = $this->select($query, $bindings);
			// print_r(reset($records));
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
                $error = $this->pdo->lastError;
                throw new Exception("There was an error with your SQL query: {$error}");
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
		public function insert($query, $bindings = array()) {
			return $this->execute($query, $bindings);
		}

		/**
		 * Run an update statement against the database.
		 *
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return int
		 */
		public function update($query, $bindings = array()) {

			$this->execute($query, $bindings);
			return $this->getAffectedRows();
		}

		/**
		 * Run an replace statement against the database.
		 *
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return int
		 */
		public function replace($query, $bindings = array()) {
			
			return	$this->execute($query, $bindings);
		}

		/**
		 * Run a delete statement against the database.
		 *
		 * @param  string  $query
		 * @param  array   $bindings
		 * @return int
		 */
		public function delete($query, $bindings = array()) {

			return $this->execute($query, $bindings);
			
		}

		/**
		 * Run a transaction on the database - uses closure to
		 * provide query being called, can take both standard and 
		 * expressive queries in the callback
		 * 
		 * @param  Closure $callback 
		 * @return mixed            
		 */
		public function transaction(Closure $callback) {
			
			// Execute the given callback within a try / catch block
			// and any exceptions are caught rollback the transaction
			// so that none of the changes are persisted to the database.
			try {

				$this->pdo->beginTransaction();
					
				$result = $callback($this);
				$this->pdo->commit();

			} catch (PDOException $e) {
				$this->pdo->rollBack();
				throw new \Exception($e->getMessage(), 500);
			}
			return $result;
		}

		/**
		 * Escape a string for usage in a query.
		 * This uses the correct quoting mechanism for the default database connection.
		 * NB: Strongly recommend that use 'Prepaired' statements
	 	 * @param  string $value String to be quoted
		 * @return string        Quoted string
		 */
		public function escape($value) {
			return $this->pdo->quote($value);
		}

		/**
		 * Returns the ID of the last inserted row
		 * @return string ID of the last row that was inserted
		 */
		public function getLastInsertID() {
			return $this->pdo->lastInsertId();
		}

		/**
		 * Returns the number of rows affected by the last prepared statement
		 * DELETE, INSERT, or UPDATE (SELECT Statements should not be trusted)
		 * @return int The number of rows affected
		 */
		public function getAffectedRows() {
			return $this->pdo->rowCount();
		}

		/**
		 * Returns an array of error information about the last 
		 * operation performed by this statement handle
		 * @return array Information about last error
		 */
		public function getLastError() {
			return $this->pdo->errorInfo();
		}

	}
}