<?php
namespace Framework\Database\Query {

	use PDO;
	use Framework\Database as Database;
	use Framework\Configuration as Configuration;

	class Sqlserver extends Database\Query {

		protected $wrapper = '[%s]';

		protected $datetime = 'Y-m-d H:i:s.000';

		public function all() {

			$sql = $this->_buildSelect();

			list($statement, $result) = $this->connector->execute($sql, $this->bindings);

			if (!$result) {
				$error = $this->connector->lastError;
				throw new \Exception("This was an error with your SQL query: {$error}");
			}

			$fetchStyle = Configuration::get('database.fetch');
			if($fetchStyle === PDO::FETCH_CLASS) {
				return $this->connector->fetch($statement, PDO::FETCH_CLASS, 'stdClass');
			} else {
				return $this->connector->fetch($statement, $fetchStyle);
			}
		}

		/**
		 * Determine basic SELECT clause
		 * @return string 
		 */
        public function select() {

            if ($this->distinct) {
				$select = 'SELECT DISTINCT';
			} else if($this->aggregate){
				$select = "SELECT {$this->_aggregate}({$this->fields[0]})";
			} else {
				$select = 'SELECT';
			}

            // Instead of using a "LIMIT" keyword, SQL Server uses the TOP keyword
            // within the SELECT statement. So, if we have a limit, we will add
            // it to the query here if there is not an OFFSET present.
            if ($this->_limit > 0 and $this->_offset <= 0) {
                $select .= 'TOP '.$this->_limit.' ';
                $this->_limit = '';
            }

            return $select;
       	}
    }
}