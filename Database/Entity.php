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
    public $_select;
    
    public function __construct(
        \Of\Database\Connection $Connection,
        \Of\Database\Sql\Select $Select
    ){
        $this->_connection = $Connection;
        $this->_select = $Select;
    }

    public function getConnection(){
        return $this->_connection->getConnection();
    }
}