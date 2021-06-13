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
                \PDO::ATTR_PERSISTENT => isset($config['ATTR_PERSISTENT']) ? $config['ATTR_PERSISTENT'] : false
            ));
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
        try {
            $sth->execute($unsecured);
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
            return  $result;
        } catch (\Exception $e){
            throw new \Exception("Could not execute query :" . $e->getMessage());
        }
    }

    public function insert($tablename, $data){
        $insert = new \Of\Database\Sql\Insert();
        $qry = $insert->prepareData($data)->insert($tablename);

        $dbh = $this->getConnection();
        $tmt  = $dbh->prepare($qry);

        try {
            $dbh->beginTransaction();
            $tmt->execute($insert->unsecureValue);
            $lastId = $dbh->lastInsertId(); 
            $dbh->commit();

            return $lastId;
        }catch (\Exception $e){
            $dbh->rollback();
            throw $e;
        }
    }

    /**
     * prepare query and try to update entry
     * @param $select instance of \Of\Database\Sql\Select
     */
    public function update(\Of\Database\Sql\Select $select, $fields, $data, $tablename){
        $update = new \Of\Database\Sql\Update();
        foreach ($fields as $key => $field) {
            $select->where($key)->eq($field);
        }

        $qry = $update->prepareData($data)->updateQry($tablename, $select);

        $dbh = $this->getConnection();
        $stmt  = $dbh->prepare($qry);

        try {
            $dbh->beginTransaction();
            $stmt->execute($update->unsecureValue);
            $rowCount = $stmt->rowCount(); 
            $dbh->commit();

            return $rowCount;
        }catch (\Exception $e){
            $dbh->rollback();
            throw $e;
        }
    }

    /**
     * prepare query and try to update entry
     * @param $select instance of \Of\Database\Sql\DeleteStatement
     */
    public function _delete(\Of\Database\Sql\DeleteStatement $delete){
        $dbh = $this->getConnection();
        $qry = $delete->getQuery();
        $stmt = $dbh->prepare($qry);
        try {
            $dbh->beginTransaction();
            $stmt->execute($delete->_whereStatement->unsecureValue);
            $rowCount = $stmt->rowCount(); 
            $dbh->commit();

            return $rowCount;
        }catch (\Exception $e){
            $dbh->rollback();
            throw $e;
        }
    }
}
?>