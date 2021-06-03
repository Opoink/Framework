<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

use \Of\Database\Migration\Columns;

class Migrate {

    protected $vendorName;
    protected $moduleName;
    protected $_config;

    /**
     * instance of \Of\Database\Connection
     */
    protected $_connection;
    
    public function __construct(
        \Of\Database\Connection $Connection
    ){
        $this->_connection = $Connection;
    }

    /**
     * set the vendor name
     */
    public function setVendorName($vendorName){
        $this->vendorName = $vendorName;
        return $this;
    }

    /**
     * set the module name
     */
    public function setModuleName($moduleName){
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * set the system config
     */
    public function setConfig($Config){
        $this->_config = $Config;
        return $this;
    }

    /**
     * initialize the migration for the current vendor_module
     */
    public function init(){
        $targetDir = ROOT . DS . 'App' . DS . 'Ext' . DS . $this->vendorName . DS . $this->moduleName . DS . 'Schema' . DS . 'tables';

        $installedTableNames = [];
        if(is_dir($targetDir)){
            $files = $this->getDirFiles($targetDir);
            if($files){
                foreach ($files as $key => $file) {
                    $_file = $targetDir.DS.$file;
                    if(file_exists($_file)){
                        $tableName = $this->getTableNameFromFileName($_file);
                        if(!$tableName){
                            throw new \Exception("Invalid JSON schema: " . $_file, 1);
                        }

                        $fields = json_decode(file_get_contents($_file), true);
                        if(json_last_error() == JSON_ERROR_NONE){ /** to make sure that the json file was no error */
                            $installedTableNames[] = $this->createTable($tableName, $fields, $_file);
                        } else {
                            throw new \Exception("Invalid JSON schema: " . json_last_error_msg() . ' --- ' . $_file, 1);
                        }
                    }
                }
            }
        }
        return $installedTableNames;
    }

    /**
     * since we assume that all files are in JSON format 
     * and the filename is actually the table of the database
     * we will try to extract the filename and clean the string
     * to get a valid table name
     */
    protected function getTableNameFromFileName($file){
        $info = pathinfo($file);

        $name = null;
        if(isset($info['filename'])){
            $name = preg_replace("/[^A-Za-z0-9 ]/", '_', $info['filename']);
            $name = strtolower($name);
        }
        return $name;
    }

    /**
     * create the table but to ensure the it is not already installed 
     * we will check it inside the information schema
     */
    protected function createTable($tableName, $fields, $_file){

        $databaseName = $this->_connection->getConfig('database');
        $tableName = $this->_connection->getTablename($tableName);

        $isExist = $this->fetchTableName($tableName);
 
        $columns = [];
        if(isset($fields['fields']) ){
            $columns = $fields['fields'];
        }
        if(!$isExist) {
            if(count($columns)){
                $_columns = new Columns();
                foreach ($columns as $keyColumn => $valueColumn) {
                    $_columns->addColumn($valueColumn, $_file);
                }
                $cols = implode(', ', $_columns->getColumns());

                $primaryKey = '';
                if (array_key_exists('primary_key', $fields)) {
                    $primaryKey = ' , PRIMARY KEY (`'.$fields['primary_key'].'`) ';
                }
    
                /** in this part the table is not exist so we have to create it */
                $sql = "CREATE TABLE IF NOT EXISTS `".$tableName."` (".$cols.$primaryKey.")ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $connection = $this->_connection->getConnection()->getConnection();
                $connection->exec($sql);

                return $tableName;
            }
        } else {
            /** 
             * the table was already exisitng here
             * so we have to check each field if existing or not 
             * if the field is not exist we have to do altering the table
             */
            foreach ($columns as $key => $column) {
                if(isset($column['name'])){
                    $isColumnExist = $this->fetchColumnName($tableName, $column['name']);
                    if(!$isColumnExist){
                        $_columns = new Columns();
                        $_columns->addColumn($column, $_file);
                        $cols = implode(', ', $_columns->getColumns());

                        $sql = "ALTER TABLE `".$tableName."` ";
                        $sql .= "ADD " . $cols . ";";

                        $connection = $this->_connection->getConnection()->getConnection();
                        $connection->exec($sql);

                        return $tableName;
                    }
                } else {
                    throw new \Exception("Invalid column: name is required " . json_encode($column));
                }
            }
        }
    }

    /**
     * instad of trying to execute query to alter tale
     * this will query the column into the table first to
     * check if it is exist or not
     * @param $tableName string
     * @param $columnName string
     */
    public function fetchColumnName($tableName, $columnName){
        $connection = $this->_connection->getConnection()->getConnection();
        $sql = "SELECT `".$columnName."` FROM `".$tableName."` limit 1";

        $isExist = $this->executeQuery($connection, $sql);
        return $isExist;
    }

    /**
     * try to query the table name to check if it is exist or not
     * return boolean
     * @param $tableName string the table name to be checked
     */
    public function fetchTableName($tableName){
        $connection = $this->_connection->getConnection()->getConnection();
        $sql = "SELECT * FROM `".$tableName."` limit 1";
        
        $isExist = $this->executeQuery($connection, $sql);
        return $isExist;
    }

    private function executeQuery($connection, $sql){
        $isExist = false;
        try {
            $sth = $connection->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $sth->execute([]);
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
            $isExist = true;
        } catch (\Exception $e) {
            $isExist = false;
        }
        return $isExist;
    }

    /**
     * return the files inside dir
     */
    protected function getDirFiles($dir){
        if(is_dir($dir)){
            $files = scandir($dir);
            if(count($files) >= 3){
                unset($files[0]);
                unset($files[1]);
            }
            sort($files);

            return $files;
        }
    }
}
?>