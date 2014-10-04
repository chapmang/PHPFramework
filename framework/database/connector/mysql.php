<?php
namespace Framework\Database\Connector {

	use PDO;

	/**
	 * Connector for MYSQl databases via a PDO instance
	 * Allows both expressive query creation (see database/query),
	 * along with direct SQL queries
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package  Framework\Database
	 */
	class Mysql extends Connector{

		/**
		 * Connect to database via PDO instance using supplied details
		 * @param array $config Connection configuration settings
		 * @return object Connection to MYSQL database
		 */
		public function connect($config) {

			extract($config);
			try {
				$dsn = "mysql:host{$host};port={$port};dbname={$database}";

				// The UNIX socket option allows the developer to indicate that the MySQL
				// instance must be connected to via a given socket. We'll just append
				// it to the DSN connection string if it is present.
				if (isset($this->_unix_socket)) {
					$dsn .= ";unix_socket={$unix_socket}";
				}
				$connection = new PDO($dsn, $username, $password, $this->_options($config));
				$connection->prepare("SET NAMES '{$charset}'")->execute();
			} catch (PDOException $e) {
				throw new \Exception("Unable to connect to the Database", 500);	
			}
			return $connection;
		}
	}
}