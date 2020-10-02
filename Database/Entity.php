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
}