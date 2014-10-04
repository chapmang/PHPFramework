<?php
namespace Framework {

    use Framework\Base as Base;
    use Framework\Event as Event;
    use Framework\Configuration as Configuration;
    use Framework\Database\Connection as Connection;

    /**
     * Database
     * Factory class for initializing the requested
     * database connector class
     * @version 1.0
     * @author Geoff Chapman <geoff.chapman@mac.com>
     * @package Framework
     */
    
    class Database extends Base {

        /**
         * The established database connections
         * @readwrite
         */
        protected static $_connections = array();

        /**
         * Create a new database connector instance using configuration
         * defined in application/configuration/Database
         * @return Database\Connectors\Connector
         */
        public static function connection($type = null) {
            Event::fire("framework.database.connect.before", array($type));

            if (is_null($type)) {
                $type = Configuration::get('database.default');
            }

            if (!isset(static::$_connections[$type])) {
                $options = Configuration::get('database.connections.' . $type);

                if (is_null($options)) {
                    throw new \Exception("Database connection is not defined for " . $type, 500);
                }

                static::$_connections[$type] = new Connection(static::connect($options), $type);
            }

            Event::fire("framework.database.connect.after", array($type));
            return static::$_connections[$type];
        }

        protected static function connect($options) {
            return static::connector($options['driver'])->connect($options);
        }

        protected static function connector($type) {

            switch ($type) {
                case "mysql":
                    return new Database\Connector\Mysql;
                    break;
                case "sqlsrv":
                    return new Database\Connector\Sqlsrv;
                    break;
                case "oracle":
                    return new Database\Connector\Oracle;
                    break;
                default:
                    throw new \Exception("Database driver [{$type}] is not supported.", 500);
                    break;
            }
        }

    /**
     * Magic Method for calling methods on the default database connection.
     *
     * <code>
     *      // Get the driver name for the default database connection
     *      $driver = DB::driver();
     *
     *      // Execute a expressive query on the default database connection
     *      $users = DB::table('users')->get();
     * </code>
     */
    public static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::connection(), $method), $parameters);
    }

        protected function _getExceptionForImplementation($method) {
                return new \Exception("The method \"{$method}\" was not implemented");
        }
    }
}