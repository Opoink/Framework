<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

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

                        $this->createTable($tableName);
                        // var_dump($fields);
                        // die;


                        // $fields = json_decode(file_get_contents($_file), true);
                    }
                }
            }
        }
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
    protected function createTable($tableName){

        $databaseName = $this->_connection->getConfig('database');
        $tableName = $this->_connection->getTablename($tableName);

        $r = $this->fetchTableName($databaseName, $tableName);

        if($r['count'] == 0 || $r['count'] == '0') {
            /** in this part the table is not exist so we have to create it */
            $sql = "CREATE TABLE IF NOT EXISTS `".$tableName."` (Prename VARCHAR( 50 ) NOT NULL)";
            $connection = $this->_connection->getConnection()->getConnection();
            $connection->exec($sql);

            $r = $this->fetchTableName($databaseName, $tableName);
            var_dump($r);
            die;
        } else {
            /** 
             * the table was already exisitng here
             * so we have to check each field if existing or not 
             * if the field is not exist we have to do altering the table
             */
        }
    }

    /**
     * get the table name in information schema
     */
    public function fetchTableName($databaseName, $tableName){
        $connection = $this->_connection->getConnection()->getConnection();
        $sql = "SELECT COUNT(*) AS `count` FROM `information_schema`.`tables` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ?";
        $unsecured = [
            $databaseName,
            $tableName
        ];

        $sth = $connection->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute($unsecured);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result[0];
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