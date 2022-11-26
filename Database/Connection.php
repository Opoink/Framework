<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database;

class Connection {

    /**
     * hold's the config value for database connection
     * this variable is set during the opoink installation
     */
    private $config = [
        'driver' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'host' => '',
        'table_prefix' => '',
    ];

    /**
     * this holds the current database connection
     * this will be closed before the application ends
     */
    private $connection;

    /**
     * set the config of database connection
     * if the parameters has value
     * the config from <installation dir>/etc/database.php will be overiden
     * @param $driver currently only "Pdo_Mysql" driver is supported
     * @param $username
     * @param $password
     * @param $database
     * @param $host
     * @param $table_prefix is the prefix added during the installation
     */
    public function setConfig($driver=null, $username=null, $password=null, $database=null, $host=null, $table_prefix=null){
        if($driver != null && $username != null && $password != null && $database != null && $host != null){
            $this->config = [
                'driver' => $driver,
                'username' => $username,
                'password' => $password,
                'database' => $database,
                'host' => $host,
                'table_prefix' => empty($table_prefix) ? '' : $table_prefix,
            ];
        } else {
            $configFile = ROOT.DS.'etc'.DS.'database.php';
            if(file_exists($configFile)){
                $this->config = include($configFile);
            }
        }
        return $this;
    }

    /**
     * return all config if the param is not set,
     * if the param is set return the value of the param
     * else return null
     */
    public function getConfig($param=null){
        if($param){
            if(isset($this->config[$param])){
                return $this->config[$param];
            }
        } else {
            return $this->config;
        }
    }

    /**
     * connect to database depends on the set driver
     * opoink currently suported driver is "Pdo_Mysql"
     * other driver will be added on the future updates
     */
    public function connect(){
        switch ($this->config['driver']) {
            case 'Pdo_Mysql':
                $this->connection = new \Of\Database\Drivers\PdoDriver();
                $this->connection->connect($this->config);
                break;
            default:
                $this->connection = null;
        }
    }

    public function getConnection(){
        if($this->connection){
            return $this->connection;
        } else {
            $this->setConfig()->connect();
            return $this->getConnection();
        }
    }

    /**
     * return table name
     * with prefix if set 
     */
    public function getTablename($tableName){
        if(!$this->config['driver']){
            $this->setConfig();
        }
        return $this->config['table_prefix'] . $tableName;
    }
}
?>
