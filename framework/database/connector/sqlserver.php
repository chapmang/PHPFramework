<?php
namespace Framework\Database\Connector {

	use PDO;

	/**
	 * Connector for SQL Server databases via a PDO instance
	 * Allowes both expressive query creation (see database/query)
	 * along with direct SQL queries
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework\Database
	 */
	class Sqlserver extends Connector {

		/**
		 * Connect to database via PDO instance using supplied details
		 * @param array $config Connection configuration settings
		 * @return object Connection to SQL Server database
		 */
		public function connect($config) {

			extract($config);
			try {
				// Format the SQL Server connection string. This connection string format can
				// also be used to connect to Azure SQL Server databases. The port is defined
				// directly after the server name, so we'll create that first.
				$port = (isset($port)) ? ','.$port : '';

				$dsn = "sqlsrv:Server={$host}{$port};Database={$database}";

				$connection = new PDO($dsn, $username, $password, $this->options($config));
			} catch (PDOException $e) {
				throw new \Exception("Unable to connect to the Database", 500);
			}
			return $connection;
		}
	}
}