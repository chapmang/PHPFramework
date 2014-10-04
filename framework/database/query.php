<?php
namespace Framework\Database {

	use Framework\Base as Base;
	use Framework\ArrayMethods as ArrayMethods;
    use Framework\Database as Database;
    use Framework\Configuration as Configuration;
    use PDO;

	/**
	 * Query
	 * Build Expressive SQL queries
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 * @subpackage Database
	 * @todo Extend variations of 'WHERE' clauses
	 * @todo Extend to handle different types of 'JOIN'
	 */
	
	class Query extends Base {

		/**
		 * Database connection
		 * @var Connection
		 * @readwrite
		 */
		protected $_connector;

		/**
		 * @readwrite
		 */
		protected $_prefix = '';

		/**
		 * @readwrite
		 */
		protected $_distinct = false;

		/**
		 * @readwrite
		 */
		protected $_aggregate = false;

		/**
		 * The table name
		 * @var  string 
		 * @read
		 */
		protected $_from;

		/**
		 * The column names
		 * @var mixed 
		 * @read
		 */
		protected $_fields;

		/**
		 * The LIMIT value
		 * @var int 
		 * @read
		 */
		protected $_limit;

		/**
		 * The OFFSET value
		 * @var int 
		 * @read
		 */
		protected $_offset;

		/**
		 * The ORDER BY clause
		 * @var string
		 * @read
		 */
		protected $_order;

		/**
		 * The ORDER direction
		 * @var string
		 * @read
		 */
		protected $_direction;

		/**
		 * @read
		 */
		protected $_join = array();

		/**
		 * @read
		 */
		protected $_wheres = array();

		/**
		 * @read
		 */
		protected $_bindings = array();

		/**
		 * @readwrite
		 */
		protected $_datetime = 'Y-m-d H:i:s';

		/**
		 * @readwrite
		 */
		protected $wrapper = '"%s"';

		/**
		 * Wrap passed $value in the applicable quotation marks
		 * allowing it to be added to the query in syntactically
		 * correct form
		 * NB: Not required if using a PDO connection
		 * @param  mixed $value Value to be escaped
		 * @return mixed        Escaped value
		 */
		protected function _quote($value) {

			// For strings escape any special characters
			// using the appropriate style for the connection
			// then apply quotes and return
			if (is_string($value)) {
				$escaped = $this->connector->escape($value);
				return "'{$escaped}'";
			}

			// For arrays access each internal value and quote
			// as appropriate then rebuild the array, apply 
			// parentheses and return
			if (is_array($value)) {
				$buffer = array();

				foreach ($value as $i) {
					array_push($buffer, $this->_quote($i));
				}

				$buffer = implode(", ", $buffer);
				return "({$buffer})";
			}

			// For null values return in the syntactically
			// correct form for SQL
			if (is_null($value)) {
				return "NULL";
			}

			// For boolean
			if (is_bool($value)) {
				return (int)$value;
			}

			return $this->connector->escape($value);
		}

		/**
		 * Build a SELECT statement using stored fields
		 * and template via sprintf
		 * @return string Constructed SELECT statement
		 */
		protected function _buildSelect() {

			$fields = array();
			$where = $order = $limit = $join = "";
			$template = "%s %s FROM %s %s %s %s %s"; // Template for SQL SELECT statement
			
			$select = $this->select();
			
			// FROM fields and any aliases
			if (!$this->_aggregate) {
				foreach($this->fields as $table => $_fields) {
					foreach ($_fields as $field => $alias) {
						if (is_string($field)) {
							$fields[] = "{$table}.{$field} AS {$alias}";
						} else {
							$fields[] = "{$table}.{$alias}";
						}
					}
				}
			}

			$fields = $this->_columns($fields);

			// JOINS (Natural only)
			$_join = $this->join;
			if (!empty($_join)){
				$join = implode(" ", $_join);
			}

			// WHERE properties
			$_wheres = $this->wheres;
			if (!empty($_wheres)) {
				foreach ($_wheres as $_where) {
					$joined[] = $_where['connector'] . ' ' . $this->{$_where['type']}($_where);
				}

				// Strip out first connector
				if (isset($joined)) {
					$where = "WHERE ". preg_replace('/AND |OR /', '', implode(' ', $joined), 1);
				}
			}

			// ORDER clause
			$_order = $this->order;
			if (!empty($_order)) {
				$_direction = $this->direction;
				$order = "ORDER BY {$_order} {$_direction}";
			}

			// LIMIT clause
			$_limit = $this->limit;
			if (!empty($_limit)) {
				$_offset = $this->offset;
				if ($_offset) {
					$limit = " LIMIT {$_limit}, {$_offset}";
				} else {
					$limit = " LIMIT {$_limit}";
				}
			}


			// Map to template
			return sprintf($template, $select, $fields, $this->from, $join, $where, $order, $limit);
		}

		/**
		 * Build an INSERT statement using stored fields
		 * and template via sprintf
		 * @param  array $data  Data to be inserted into database
		 * @return string       Constructed INSERT statement
		 */
		protected function _buildInsert($data) {
				
			// Force to multidimensional array to make parameter 
			// binding a little simpler
			if (!is_array(reset($data))) {
				$data = array($data);
			} 

			$fieldNames = implode('`, `', array_keys($data[0]));
			$rowPlaces = '(' . implode(', ', array_fill(0, count($data[0]), '?')) . ')';
			$fieldValues = implode(', ', array_fill(0, count($data), $rowPlaces));

			$template = "INSERT INTO `%s` (`%s`) VALUES %s";
            foreach($data as $d) {
                $this->_bindings = array_merge($this->_bindings, array_values($d));
            }
            
			return sprintf($template, $this->from, $fieldNames, $fieldValues);
		}

		/**
		 * Build an UPDATE using stored fields
		 * and template via sprintf
		 * @param  array $data  Data to be updated in the database
		 * @return string       Constructed UPDATE statement
		 */
		protected function _buildUpdate($data) {
			
			$where = $limit = $fieldNames = "";
			$template = "UPDATE %s SET %s %s %s";

			// Force to multidimensional array to make parameter 
			// binding a little simpler
			if (!is_array(reset($data))) {
				$data = array($data);
			} 

			foreach ($data[0] as $key => $value) {
				$parts[] = "`{$key}` = ?";
			}

			$fieldNames = join(", ", $parts);
			
			foreach($data as $d) {
                $this->_bindings = array_merge(array_values($d), $this->_bindings);
            }

			// Where clause to match
			$_wheres = $this->wheres;
			if (!empty($_wheres)) {
				foreach ($_wheres as $_where) {
					$joined[] = $_where['connector'] . ' ' . $this->{$_where['type']}($_where);
				}

				// Strip out first connector
				if (isset($joined)) {
					$where = "WHERE ". preg_replace('/AND |OR /', '', implode(' ', $joined), 1);
				}	
			}

			// Limit for length of update
			$_limit = $this->_limit;
			if (!empty($limit)) {
				$_offset = $this->offset;
				$limit = "LIMIT {$_limit} {$_offset}";
			}
			return sprintf($template, $this->from, $fieldNames, $where, $limit);
		}

		/**
		 * Build a DELETE using stored fields
		 * and template via sprintf
		 * @return string Constructed DELETE statement
		 */
		protected function _buildDelete() {
			$where = $limit = "";
			$template = "DELETE FROM %s %s %s";

			// Where clause to match
			$_wheres = $this->wheres;
			if (!empty($_wheres)) {
				foreach ($_wheres as $_where) {
					$joined[] = $_where['connector'] . ' ' . $this->{$_where['type']}($_where);
				}

				// Strip out first connector
				if (isset($joined)) {
					$where = "WHERE ". preg_replace('/AND |OR /', '', implode(' ', $joined), 1);
				}	
			}

			// Limit for length of update
			$_limit = $this->_limit;
			if (!empty($limit)) {
				$_offset = $this->offset;
				$limit = "LIMIT {$_limit} {$_offset}";
			}

			return sprintf($template, $this->from, $where, $limit);
		}

		/**
		 * Determine basic select type
		 * @return string 
		 */
		protected function select() {

			if ($this->distinct) {
				$select = 'SELECT DISTINCT';
			} else if($this->aggregate){
				$select = "SELECT {$this->_aggregate}({$this->fields[0]})";
			} else {
				$select = 'SELECT';
			}

			return $select;
		}

		/**
		 * Force a query to return destinct results
		 * @return Query
		 */
		public function distinct() {

			$this->distinct = true;
			return $this;
		}

		/**
		 * Determine save type and initiate appropriate 
		 * SQl command
		 * @param  array $data Data to be saved
		 * @return mixed 	   If Insert primary key of new row     
		 */
		public function save($data) {
			$isInsert = count($this->_wheres) == 0;
			if ($isInsert) {
				$sql = $this->_buildInsert($data);
			} else {
				$sql = $this->_buildUpdate($data);
			}
			$result = $this->_connector->execute($sql, $this->_bindings);
			if ($result === false) {
				throw new \Exception("There was an error executing the 'Save' SQL", 500);	
			}
			if ($isInsert) {
				return $this->_connector->lastInsertId;
			}
			return 0;
		}

		/**
		 * Run a DELETE statement and return number of rows affected
		 * @return int Number of rows affected
		 */
		public function delete() {

			$sql = $this->_buildDelete();
			$result = $this->_connector->execute($sql, $this->_bindings);

			if ($result === false) {
				throw new \Exception ("There was an error executing the 'Delete' SQL", 500);
			}
			return $result;
		}


		/**
		 * Specify which table the data should be read from
		 * or written to
		 * @param  string $from   The table to be queried
		 * @param  array  $fields Optional array of fields to return
		 */
		public function from($from, $fields = array("*")) {
			if (empty($from)) {
				throw new \Exception("Invalid argument", 500);
			}

			$this->_from = $this->_wrapTable($from);
			if ($fields) {
				$this->_fields[$from] = $fields;
			}
			return $this;
		}

		/**
		 * Specify a natural JOIN across tables
		 * @param  string $join   Table to join with
		 * @param  string $on     Field on which join is dependent
		 * @param  array  $fields Optional array of fields to return
		 */
		public function join($join, $on, $fields = array()) {
			if (empty($join)) {
				throw new \Exception("Invalid argument", 500);
			}
			if (empty($on)) {
				throw new \Exception("Invalid argument", 500);
			}
			$this->_fields += array($join => $fields);
			$this->_join[] = "JOIN {$join} ON {$on}";
			return $this;
		}

		/**
		 * Specify how many rows to return and (optionally) 
		 * on which page to begin the results
		 * @param  int  $limit The number of rows to return
		 * @param  integer $page  Optional page to begin results
		 */
		public function limit($limit, $page = 1) {
			
			if (empty($limit)) {
				throw new \Exception("Invalid argument", 500);
			}
			$this->_limit = $limit;
			$this->_offset = $limit * ($page - 1);
			return $this;
		}

		/**
		 * Specify which field to order query by and
		 * in which direction
		 * @param  string $order     Field to order by
		 * @param  string $direction Direction to sort order
		 */
		public function order($order, $direction = "asc") {

			if (empty($order)) {
				throw new \Exception("Invalid argument", 500);
			}
			$this->_order = $order;
			$this->_direction = $direction;
			return $this;
		}

		/**
		 * Add a where condition to the query
		 * @param  string $field     Field in table to match
		 * @param  string $operator  Operator defining where relationship
		 * @param  mixed $value      Value to be matched
		 * @param  string $connector Connector to chained where statements (optional)
		 * @return Query            
		 */
		public function where($field, $operator = null, $value = null, $connector = 'AND') {
			
			if (empty($field)) {
				throw new \Exception("Invalid argument", 500);
			}
			$type = "_where";
			$this->_wheres[] = compact('type', 'field', 'operator', 'value', 'connector');
			$this->_bindings[] = $value;
			return $this;
		}

		/**
		 * Add an OR where condition to the query
		 * @param  string $field    Field in table to match
		 * @param  string $operator Operator defining where relationship
		 * @param  mixed $value     Value to be matched
		 * @return Query           
		 */
		public function orWhere($field, $operator = null, $value = null) {
			
			return $this->where($field, $operator, $value, 'OR');
		}

		/**
		 * Add a BETWEEN condition to the query
		 * @param  string  $field     Field in table to match
		 * @param  mixed  $min        Minimum value of range
		 * @param  mixed  $max        Maximum value of range
		 * @param  string  $connector Connector to chained where statements
		 * @param  boolean $not       Key for running NOT BETWEEN
		 * @return Query
		 */
		public function whereBetween($field, $min, $max, $connector = 'AND',  $not = false) {
			
			$type = ($not) ? '_whereNotBetween' : '_whereBetween';
			$this->_wheres[] = compact('type', 'field', 'min', 'max', 'connector');
			$this->_bindings[] = $min;
			$this->_bindings[] = $max;
			return $this;
		}

		/**
		 * Add a NOT BETWEEN condition to the query
		 * @param  string $field     Field in table to match
		 * @param  mixed  $min        Minimum value of range
		 * @param  mixed  $max        Maximum value of range
		 * @param  string  $connector Connector to chained where statements
		 * @return Query
		 */
		public function whereNotBetween($field, $min, $max, $connector = 'AND') {
			
			return $this->whereBetween($field, $min, $max, $connector, true);
		}

		public function whereIn($field, $values, $connector = 'AND', $not = false) {
			
			$type = ($not) ? '_whereNotIn' : '_whereIn';
			$this->_wheres[] = compact('type', 'field', 'values', 'connector');
			$this->_bindings = array_merge($this->_bindings, $values);
			return $this;
		}

		public function whereNotIn($field, $values, $connector = 'AND') {

			return $this->whereIn($field, $values, $connector, true);
		}

		/**
		 * Return the first row of a query result
		 * @return mixed
		 */
		public function first() {
			
			// Clone limit and offset values
			$limit = $this->_limit;
			$offset = $this->_offset;

			// Retrieve first row
			$this->limit(1);
			$all = $this->all();
			$first = ArrayMethods::first($all);

			// Reset limit and offset
			if ($limit) {
				$this->_limit = $limit;
			}
			if ($offset) {
				$this->_offset = $offset;
			}

			return $first;
		}

		/**
		 * Return the average value
		 * @param string $values Column name
		 * @return int Number of rows in query
		 */
		public function average($values = NULL) {

			if (!$values){
				$error = $this->connector->lastError;
				throw new \Exception("No column was defined", 500);	
			}

			$avg = $this->_aggregate('avg', $values);
			return $avg;
		}

		/**
		 * Return the number of rows in a single query on a table
		 * @param string $value Column name (can also use 1 quick count rows)
		 * @return int Number of rows in query
		 */
		public function count($values = '*') {
			
			$count = $this->_aggregate('count', $values);
			return $count;
		}

		/**
		 * Return the maximum value of an expression in a SELECT statment
		 * @param string $value Numeric field to be checked (can also be a formula)
		 * @return int Number of rows in query
		 */
		public function max($values = NULL) {

			if (!$values){
				$error = $this->connector->lastError;
				throw new \Exception("No column was defined", 500);	
			}

			$max = $this->_aggregate('max', $values);
			return $max;
		}

		/**
		 * Return the minimum value of an expression in a SELECT statment
		 * @param string $value Numeric field to be checked (can also be a formula)
		 * @return int Number of rows in query
		 */
		public function min($values = NULL) {

			if (!$values){
				$error = $this->connector->lastError;
				throw new \Exception("No column was defined", 500);	
			}

			$max = $this->_aggregate('min', $values);
			return $max;
		}

		/**
		 * Return the sum of an expression in a SELECT statement
		 * @param string $value Numeric field to be checked (can also be a formula)
		 * @return int Number of rows in query
		 */
		public function sum($values = NULL) {

			if (!$values){
				$error = $this->connector->lastError;
				throw new \Exception("No column was defined", 500);	
			}

			$max = $this->_aggregate('sum', $values);
			return $max;
		}

		/**
		 * Run the select query for a supported SQL Aggregate function
		 * @param string $method Aggregate function to be used
		 * @param string $value Parameters to be used in SQl query
		 * @return mixed Results of SQL query
		 */
		protected function _aggregate($method, $values) {

			$this->_aggregate = strtoupper($method);
			$this->_fields = array($values);
			$all = $this->all();
			$aggregate = ArrayMethods::first($all);
			return $aggregate;
		}

		/**
		 * Write a 'WHERE' statement
		 * @param  array $where  Parts for single 'WHERE' statement
		 * @return string        Formatted 'WHERE' statement
		 */
		protected function _where($where) {

			$parameter = $this->_parameterize($where['value']);
			return $this->_wrap($where['field']) . ' ' . $where['operator'] . ' ' . $parameter;
		}

		/**
		 * Write a 'BETWEEN' statement
		 * @param  array $where  Parts for a single 'BETWEEN' statement 
		 * @return string        Formatted 'BETWEEN' statement
		 */
		protected function _whereBetween($where) {

			$min = $this->_parameterize($where['min']);
			$max = $this->_parameterize($where['max']);
			return $where['field'] . ' BETWEEN ' . $min . ' AND ' . $max;
		}

		/**
		 * Write a 'NOT BETWEEN' statement
		 * @param  array $where  Parts for a single 'NOT BETWEEN' statement
		 * @return string        Formatted 'NOT BETWEEN' statement
		 */
		protected function _whereNotBetween($where) {
			
			$min = $this->_parameterize($where['min']);
			$max = $this->_parameterize($where['max']);
			return $where['field'] . ' NOT BETWEEN ' . $min . ' AND ' . $max;
		}

		/**
		 * Write an 'IN' statement
		 * @param  array $where Parts for an 'IN' statement 
		 * @return string       Formatted 'IN' statement
		 */
		protected function _whereIn($where) {

			$parameters = $this->_parameterize($where['values']);
			return $where['field']. ' IN ('. $parameters .')';
		}

		/**
		 * Write a 'NOT IN' statement
		 * @param  array $where Parts for a 'NOT IN' statement
		 * @return string       Formatted 'IN' statement
		 */
		protected function _whereNotIn($where) {

			$parameters = $this->_parameterize($where['values']);
			return $where['field']. ' NOT IN ('. $parameters .')';
		}


		/**
		 * Build query parameter string
		 * @param  array $values Values to be parametrized
		 * @return string        Parameter string for query
		 */
		protected function _parameterize($values) {

			$parameters = str_repeat('?,', count($values) - 1) . '?';
			return $parameters;
		}

		/**
		 * Wrap a table in key word identifiers
		 * 
		 * @param  string $table the table name to be wrapped
		 * @return string
		 */
		protected function _wrapTable($table) {

			$prefix = '';

			// Allow tables may be prefixed with a string. enables the prefixing
			// of tables by application on the same database.
			if (isset($this->_prefix)) {

				$prefix = $this->_prefix;
			}

			return $this->_wrap($prefix.$table);
		}

		/**
		 * Wrap a value in keyword identifiers
		 * 
		 * @param  string $value Value to be wrapped
		 * @return string        
		 */
		protected function _wrap($value) {

			// Cover the wrapping of values that contain a column alias,
			// by wrapping each segment.
			if (strpos(strtolower($value), ' as ') !== false) {

				$parts = explode(' ', $value);

				return sprintf('%s AS %s', $this->_wrap($parts[0]), $this->_wrap($parts[2]));
			}

			// If a column is prefixed by its table name wrap both
			// the table and column keyword identifiers
			$parts = explode('.', $value);

			foreach ($parts as $key => $value) {
				
				if ($key == 0 and count($parts) > 1) {
					$wrapped[] = $this->_wrapTable($value);
				} else {
					$wrapped[] = $this->_wrapValue($value);
				}
			}

			return implode('.', $wrapped);
		}

		/**
		 * Wrap a single string in keyword identifiers
		 * 
		 * @param  string $value String value to be wrapped
		 * @return string        
		 */
		protected function _wrapValue($value) {

			if ($value !== '*') {
				return sprintf($this->wrapper, $value);
			}

			return $value;
		}

		/**
		 * Create a comma separated list of wrapped column names
		 * @param  array $columns Column names to be wrapped
		 * @return string          
		 */
		protected function _columns($columns) {
			
			return implode(', ', array_map(array($this, '_wrap'), $columns));
		}


		protected function _getExceptionForImplementation($method) {
			return new \Exception("{$method} method not implemented");
		}
	}
}