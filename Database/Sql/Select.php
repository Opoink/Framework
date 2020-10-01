<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql;

class Select {

    public $_fromStatement;
    public $_columnStatement;
    public $_whereStatement;

    public function __construct(
        \Of\Database\Sql\Statements\From $From,
        \Of\Database\Sql\Statements\Column $Column,
        \Of\Database\Sql\Statements\Where $Where
    ){
        $this->_fromStatement = $From;
        $this->_columnStatement = $Column;
        $this->_whereStatement = $Where;
    }

    /**
     * set the table name where to fetch data from database
     * @param $colNames either string or array
     * return this instance
     */
    public function select($colNames=null){
        $this->_columnStatement->parseValue($colNames);
        return $this;
    }

    /**
     * set the table name where to fetch data from database
     * @param $tableName either string or array
     * return this instance
     */
    public function from($tableName){
        $this->_fromStatement->parseValue($tableName);
        return $this;
    }

    /**
     * add filter where to sql query
     * @param string
     */
    public function where($where, $condition, $value){
        $this->_whereStatement->where($where, $condition, $value);
        return $this;
    }

    /**
     * return query query string
     */
    public function getQuery(){
        $query = "SELECT ";
        $query .= $this->_columnStatement->getColumns();
        $query .= $this->_fromStatement->getFrom();

        echo $query;
        die;
    }

    
}