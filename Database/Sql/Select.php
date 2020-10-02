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
     * this filter can be used for WHERE and AND operators
     * @param string
     */
    public function where($where){
        $this->_whereStatement->where($where);
        return $this;
    }

    /**
     * add the value equals condition from previous where statement
     * @param $value string
     */
    public function eq($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::EQ, $value);
        return $this;
    }

    /**
     * add the value not equals condition from previous where statement
     * @param $value string
     */
    public function ne($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::NE, $value);
        return $this;
    }

    /**
     * add the value less than condition from previous where statement
     * @param $value string
     */
    public function lt($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::LT, $value);
        return $this;
    }

    /**
     * add the value less than or equals condition from previous where statement
     * @param $value string
     */
    public function ltoe($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::LTOE, $value);
        return $this;
    }

    /**
     * add the value greater than condition from previous where statement
     * @param $value string
     */
    public function gt($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::GT, $value);
        return $this;
    }

    /**
     * add the value greater than or equals condition from previous where statement
     * @param $value string
     */
    public function gtoe($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::GTOE, $value);
        return $this;
    }

    /**
     * add the between from previous where statement
     * @param $from int
     * @param $to int
     */
    public function between($from, $to){
        $this->_whereStatement->between($from, $to);
        return $this;
    }

    /**
     * add the not between from previous where statement
     * @param $from int
     * @param $to int
     */
    public function notBetween($from, $to){
        $this->_whereStatement->notBetween($from, $to);
        return $this;
    }

    /**
     * add the in statement from previous where statement
      * @param $values array
     */
    public function in($values){
        $this->_whereStatement->in($values);
        return $this;
    }

    /**
     * add the in statement from previous where statement
      * @param $values array
     */
    public function notIn($values){
        $this->_whereStatement->notIn($values);
        return $this;
    }

    public function orWhere($where, $condition, $value){
        $this->_whereStatement->orWhere($where, $condition, $value);
        return $this;
    }

    /**
     * return query query string
     */
    public function getQuery(){
        $query = "";
        if($this->_columnStatement->isTriggered && $this->_fromStatement->isTriggered){
            $query .= "SELECT ";
            $query .= $this->_columnStatement->getColumns();
            $query .= $this->_fromStatement->getFrom();
        }
        $query .= $this->_whereStatement->getWhere();

        return  $query;
    }

    
}