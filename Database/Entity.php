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
        return $this->getConnection()->fetchAll($select->getQuery(), $select->_whereStatement->unsecureValue);
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
}