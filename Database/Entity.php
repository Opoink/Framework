<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database;

class Entity {

    const COLUMNS = [];
	
	protected $tablename;
    protected $primaryKey;

    protected $data = [];

    /**
     * instance of \Of\Database\Connection
     */
    protected $_connection;

    public $_di;
    
    public function __construct(
        \Of\Database\Connection $Connection
    ){
        $this->_connection = $Connection;
        $this->_di = new \Of\Std\Di();
    }

    public function getConnection(){
        return $this->_connection->getConnection();
    }

    public function getSelect(){
        $di = new \Of\Std\Di();
        return $di->get('\Of\Database\Sql\Select');
    }

    public function fetchAll(\Of\Database\Sql\Select $select){
        $data = $this->getConnection()->fetchAll($select->getQuery(), $select->_whereStatement->unsecureValue);
        return $this->setCollection($data);
    }

    /**
     * set the collected data and put that into an array
     * of current class instance
     */
    public function setCollection($data){
        $result = null;
        if(count($data) == 1){
            if(isset($data[0])){
                $result = $this->_di->make(get_class($this));
                $result->setData($data[0]);
            }
        } else {
            $result = [];
            foreach($data as $d){
                $newDataEntity = $this->_di->make(get_class($this));
                $newDataEntity->setData($d);
                $result[] = $newDataEntity;
            }
        }
        return $result;
    }

    public function count(\Of\Database\Sql\Select $select, $col = null, $alias=''){
        $_select = clone $select;
        $_select->count($col, $alias);
        $count = $this->fetchAll($_select);
        $_select->dumpQuery();
        if(count($count)){
            $count = $count[0];
            return $count;
        } else {
            return null;
        }
    }

    /*
    *   return table name
    *   with prefix if set 
    */
    public function getTablename($tableName=null){
        if(!$tableName) {
            $tn = $this->tablename;
        } else {
            $tn = $tableName;
        }

        return $this->_connection->getTablename($tn);
    }

    /**
     * return value of data if available
     * @param $params array key value pair
     */
    public function getByColumn($params, $limit=1){
        $mainTable = $this->getTablename();
        $s = $this->getSelect()
        ->select()
        ->from($mainTable);

        if($limit){
            $s->limit($limit);
        }
        foreach ($params as $key => $value) {
            $s->where($key)->eq($value);
        }
        
        $data = $this->fetchAll($s);
        return $data;
    }

    public function setData($key, $val=null){
        if(is_array($key)){
            foreach($key as $k => $v){
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $val;
        }
        return $this;
    }

    public function getData($key=null){
        if(!$key){
            return $this->data;
        } else {
            if(isset($this->data[$key])){
                return $this->data[$key];
            }
        }

        return null;
    }
}