<?php
namespace Framework\Database\Query {

    use PDO;
    use Framework\Database as Database;
    use Framework\Configuration as Configuration;

    
    class Mysql extends Database\Query {

        protected $wrapper = '`%s`';

        public function __construct($options = array()) {
            parent::__construct($options);
        }
        
        public function all() {
            
            $sql = $this->_buildSelect();
            var_dump($sql);
            list($statement, $result) = $this->connector->execute($sql, $this->bindings);
            
            if (!$result) {
                $error = $this->connector->lastError;
                throw new \Exception("There was an error with your SQL query: {$error}");
            }

            $fetchStyle = Configuration::get('database.fetch');
            
            if ($fetchStyle === PDO::FETCH_CLASS) {
                return $this->connector->fetch($statement, PDO::FETCH_CLASS, 'stdClass');
            } else {
                return $this->connector->fetch($statement, $fetchStyle);
            }
        }

    }
}