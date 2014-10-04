<?php
namespace Framework\Database\Query {

	use Framework\Database as Database;
	use Framework\Configuration as Configuration;

	class Oracle extends Database\Query {

		public function all() {

			$sql = $this->_buildSelect();
			list($statement, $result) = $this->connector->execute($sql, $this->bindings);

			if (!$result) {
                $error = $this->connector->lastError;
                throw new \Exception("There was an error with your SQL query: {$error}");
            }

            $fetchStyle = Configuration::get('database.fetch');
            return $this->connector->fetch($statement, $fetchStyle);
		}
	}
}