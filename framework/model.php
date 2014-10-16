<?php
namespace Framework {

	use PDO;
	use Framework\Base	as Base;
	use Framework\Registry as Registry;
	use Framework\Inspector as Inspector;
	use Framework\StringMethods as StringMethods;

	/**
	 * Model
	 * Attempt at basic ORM.
	 * contains methods for building tables - see database\sync
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 */
	class Model extends Base {

		/**
		 * @readwrite
		 */
		protected $_table;

		/**
		 * Data types to be used in tables
		 * @read
		 */
		protected $_types = array(
			"autonumber",
			"text",
			"integer",
			"decimal",
			"boolean",
			"datetime"
			);

		protected $_columns;

		protected $_primary;

	    public function __construct ($options = array()) {
	    	parent::__construct($options);

			$this->load();
		}

		/**
		 * If primary column supplied in instantiation (setter called in constructor)
		 * then retrieve the relevant record and complete the object
		 * @return [type] [description]
		 */
		public function load() {

			// Get primary column
			$primary = $this->primaryColumn;

			$raw = $primary['raw'];
			$name = $primary['name'];

			// If primary column known (else like creating new record)
			if (!empty($this->$raw)) {

				$previous = Database::query()
					->from($this->table)
					->where("{$name}", "=", $this->$raw)
					->first();

				// No matching record
				if ($previous == null) {
					throw new \Exception("Primary key value invalid", 500);
				}

				// Set property values for those not supplied in constructor
				foreach ($previous as $key => $value) {

					$prop = "_{$key}";
					if (!empty($previous->$key) && !isset($this->$prop)) {
						$this->$key = $previous->$key;
					}
				}
			}

		}

		/**
		 * Given a primary column value delete the record
		 * @return int The number of rows deleted (should always be 1!)
		 */
		public function delete() {

			$primary = $this->primaryColumn;

			$raw = $primary['raw'];
			$name = $primary['name'];

			if (!empty($this->$raw)) {
				return Database::query()
					->from($this->table)
					->where("{$name}", "=", $this->$raw)
					->delete();
			}
		}

		/**
		 * Given an array of column values delete the records
		 * @param  array  $where Clause to match
		 * @return [type]        [description]
		 */
		public static function deleteAll($where = array()) {

			$query = Database::query()
				->from($instance->table);

			foreach ($where as $clause => $value) {
				$query->where($clause, $value);
			}

			return $query->delete();
		}

		/**
		 * Save values of class properties to table related to class
		 * using the columns returned by getColumns method. Handles 
		 * inserts or updates by passing where clause (primary key)
		 * to query->save()
		 * @return int If insert primary key of new row
		 */
		public function save() {

			$primary = $this->primaryColumn;

			$raw = $primary['raw'];
			$name = $primary['name'];

			$query = Database::query()
				->from($this->table);

			// If primary key known set where clause
			if (!empty($this->$raw)) {
				$query->where("{$name}", "=", $this->$raw);
			}

			// Build data array for saving
			$data = array();
			foreach ($this->columns as $key => $column) {
				if (!$column['read']) {
					$prop = $column['raw'];
					$data[$key] = $this->$prop;
					continue;
				}

				if ($column != $this->primaryColumn && $column) {
					$method = "get" . ucfirst($key);
					$data[$key] = $this->$method();
					continue;
				}
			}

			$result = $query->save($data);

			if ($result > 0) {
				$this->$raw = $result;
			}

			return $result;
		}

		/**
		 * Return user defined table name or default to
		 * singular form of current Model's class name
		 * @return string Table name
		 */	
		public function getTable() {

			if (empty($this->_table)) {

				$this->_table = strtolower(StringMethods::singular(get_class($this)));
			}
			return $this->_table;
		}

		/**
		 * Return property metadata for any designated 
		 * with the @column flag. Includes a test for valid type
		 * @return array Column metadata
		 */
		public function getColumns() {

			if (empty($_columns)) {
				$primaries = 0;
				$columns = array();
				$class = get_class($this);
				$types = $this->types;

				// Create Inspector instance to allow reading of metadata
				$inspector = new Inspector($this);
				$properties = $inspector->getClassProperties();

				// Utility function to return the first item in a metadata array,
				// i.e., The value of the length flag
				$first = function($array, $key) {
					if (!empty($array[$key]) && count($array[$key]) == 1) {
						return $array[$key][0];
					}
					return null;
				};

				// Loop through all properties in the model
				foreach ($properties as $property) {
					
					$propertyMeta = $inspector->getPropertyMeta($property);

					// Select only those with the @column flag and
					// retrieve metadata
					if (!empty($propertyMeta["@column"])) {
						$name = preg_replace("#^_#", "", $property);
						$primary = !empty($propertyMeta["@primary"]);
						$type = $first($propertyMeta, "@type");
						$length = $first($propertyMeta, "@length");
						$index = !empty($propertyMeta["@index"]);
						$readwrite = !empty($propertyMeta["@readwrite"]);
						$read = !empty($propertyMeta["@read"]) || $readwrite;
						$write = !empty($propertyMeta["@write"]) || $readwrite;

						// Not a valid property type so throw an error
						if (!in_array($type, $types)) {
							throw new \Exception("{$type} is not a valid column type", 500);
						}

						if ($primary) {
							$primaries++;
						}

						$columns[$name] = array(
							"raw" => $property,
							"name" => $name,
							"primary" => $primary,
							"type" => $type,
							"length" => $length,
							"index" => $index,
							"read" => $read,
							"write" => $write,

						);
					}
				}

				// More than one primary column so throw an error
				if ($primaries !== 1) {
					throw new \Exception("{$class} must have exactly one @primary column", 500);					
				}

				$this->_columns = $columns;
			}
			return $this->_columns;
		}

		/**
		 * Return a single names column
		 * @param  string $name Name of column to be returned
		 * @return array        Requested column
		 */
		public function getColumn($name) {

			if (!empty($this->_columns[$name])) {
				return $this->_columns[$name];
			}
			return null;
		}

		/**
		 * Return primary column
		 * @return array Primary column
		 */
		public function getPrimaryColumn() {

			if (!isset($this->_primary)) {
				$priamry;
				foreach ($this->columns as $column) {
					if($column["primary"]) {
						$primary = $column;
						break;
					}
				}
				$this->_primary = $primary;
			}
			return $this->_primary;
		}

		/**
		 * Static wrapper of _first() method
		 * @param  array  $where     Any where clauses to be used in selection
		 * @param  array  $fields    Fields to be retrieved
		 * @param  array  $joins	 Any simple joins to be included
		 * @param  string $order     Column to have results ordered by
		 * @param  string $direction Direction of ordering to be used
		 * @return mixed             Row returned from database
		 */
		public static function first($where = array(), $fields = array("*"), $joins = array(), $order = null, $direction = null) {

			$model = new static();
			$res = $model->_first($where, $fields, $order, $direction);
			return $res;
		}

		/**
		 * Return first record matching the requested query
		 * @param  array  $where     Any where clauses to be used in selection
		 * @param  array  $fields    Fields to be retrieved
		 * @param  array  $joins	 Any simple joins to be included
		 * @param  string $order     Column to have results ordered by
		 * @param  string $direction Direction of ordering to be used
		 * @return mixed             Row returned from database
		 */
		protected function _first($where = array(), $fields = array("*"), $joins = array(), $order = null, $direction = null) {

			$query = Database::query()
				->from($this->table, $fields);
 		
			foreach ($where as $i) {
				list($clause, $operator, $value) = $i;
				$query->where($clause, $operator, $value);
			}

			if (!empty($joins)) {
				foreach ($joins as $i) {
					list($join, $on, $fields) = $i;
					$query->join($join, $on, $fields);
				}
			}

			if ($order != null) {
				$query->order($order, $direction);
			}

			$first = $query->first();

			$class = get_class($this);
	
			if ($first) {
				return new $class(
					$first
				);
			}

			return null;
		}

		/**
		 * Static wrapper of _all() method
		 * @param  array  $where     Any where clauses to be used in selection
		 * @param  array  $fields    Fields to be retrieved
		 * @param  array  $joins	 Any simple joins to be included
		 * @param  string $order     Column to have results ordered by
		 * @param  string $direction Direction of ordering to be used
		 * @param  int $limit        Limit of rows to be returned
		 * @param  int $page         Offset to be used in paginating results
		 * @return array             Rows returned from database
		 */
		public static function all($where = array(), $fields = array("*"), $joins = array(), $order = null, $direction = null, $limit = null, $page = null) {

			$model = new static();
			return $model->_all($where, $fields, $joins, $order, $direction, $limit, $page);			
		}

		/**
		 * Return all records matching the requested query
		 * @param  array  $where     Any where clauses to be used in selection
		 * @param  array  $fields    Fields to be retrieved
		 * @param  array  $joins	 Any simple joins to be included
		 * @param  string $order     Column to have results ordered by
		 * @param  string $direction Direction of ordering to be used
		 * @param  int $limit        Limit of rows to be returned
		 * @param  int $page         Offset to be used in paginating results
		 * @return array             Row returned from database
		 */
		protected function _all($where = array(), $fields = array("*"), $joins = array(), $order = null, $direction = null, $limit = null, $page = null) {

			$query = Database::query()
				->from($this->table, $fields);

			foreach ($where as $i) {
				list($clause, $operator, $value) = $i;
				$query->where($clause, $operator, $value);
			}

			if (!empty($joins)) {
				foreach ($joins as $i) {
					list($join, $on, $fields) = $i;
					$query->join($join, $on, $fields);
				}
			}

			if ($order != null) {
				$query->order($order, $direction);
			}

			if ($limit != null) {
				$query->limit($limit, $page);
			}

			$rows = array();
			$class = get_class($this);

			foreach ($query->all() as $row) {
				$rows[] = new $class(
					$row
				);
			}

			return $rows;
		}

		/**
		 * Static wrapper of _count() method
		 * @param  array  $where Any where clauses to be used in selection
		 * @return int           Number of rows selected
		 */
		public static function count($where = array()) {

			$model = new static();
			return $model->_count($where);
		}

		/**
		 * Count the number of rows matched
		 * @param  array  $where Any where clauses to be used in selection
		 * @return int           Number of rows selected
		 */
		protected function _count($where = array()) {
			$query = Database::query()
				->from($this->table);

			foreach ($where as $clause => $value) {
				$query->where($clause, $value);
			}

			return $query->count();
		}

		public function _getExceptionForImplementation($method) {

			return new \Exception("{$method} method not implemented", 500);
		}

	}
}