<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Drivers;

class PdoDriver {

    /**
     * this holds the connection from the current database
     */
    private $connection;

    public function connect($config){
        try {
            $connectionStr = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'];
            $this->connection = new \PDO( $connectionStr, $config['username'], $config['password'], array(
                \PDO::ATTR_PERSISTENT => true
            ));
        } catch (\PDOException $pe) {
            throw new \Exception("Could not connect to the database ".$config['database']." :" . $pe->getMessage());
        }
        return $this;
    }

    public function getConnection(){
        return $this->connection;
    }


    public function fetchAll($sql, $unsecured=[]){
        $sth = $this->getConnection()->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute($unsecured);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return  $result;
    }
}
?>