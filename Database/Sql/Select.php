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
     * add sub select for the sql statement
     * @param $value instance of object \Closure
     * @param $addOperator either to add operator of not
     * sample of operator is WHERE
     */
    public function group($value,  $addOperator=true){
        if($value instanceof \Closure){
            $this->_whereStatement->where('where');
            $this->_whereStatement->addConVal('', $value, $addOperator);
        } else {
            throw new \Exception('calling on group select statement of none instanceof \Closure');
        }
        return $this;
    }
    
    /**
     * add sub select for the sql statement
     * @param $value instance of object \Closure
     * @param $addOperator either to add operator of not
     * sample of operator is WHERE
     */
    public function orGroup($value,  $addOperator=true){
        if($value instanceof \Closure){
            $this->_whereStatement->orWhere('orWhere');
            $this->_whereStatement->addConVal('', $value, $addOperator);
        } else {
            throw new \Exception('calling on orGroup select statement of none instanceof \Closure');
        }
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

    /**
     * add the like statement from previous where statement
      * @param $values array
     */
    public function like($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::LIKE, $value);
        return $this;
    }

    /**
     * add the not like statement from previous where statement
      * @param $values array
     */
    public function notLike($value){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::NOTLIKE, $value);
        return $this;
    }

    /**
     * add the is null statement from previous where statement
     * @param $values array
     */
    public function isNull($isNull=true){
        $this->_whereStatement->isNull($isNull);
        return $this;
    }

    /**
     * add or condition 
     * @param $orwhere array
     */ 
    public function orWhere($orWhere){
        $this->_whereStatement->orWhere($orWhere);
        return $this;
    }

    /**
     * return query query string
     * @param $isSub boolean tells if the query is sub or not
     */
    public function getQuery($isSub=false){
        $query = "";
        if($this->_columnStatement->isTriggered && $this->_fromStatement->isTriggered){
            $query .= "SELECT ";
            $query .= $this->_columnStatement->getColumns();
            $query .= $this->_fromStatement->getFrom();
        }
        $query .= $this->_whereStatement->getWhere($isSub);

        return  $query;
    }

    /*
    *   return escaped string
    */
    protected function escape($string){
        return addcslashes((string) $string, "\x00\n\r\\'\"\x1a");
    }

    /**
     * return query query string unsercrured varialble is now change into its value
     * @param $isSub boolean tells if the query is sub or not
     */
    public function getLastSqlQuery(){
        $qry = $this->getQuery();

        foreach ($this->_whereStatement->unsecureValue as $key => $value) {
            $qry = str_replace($key, "'".$this->escape($value)."'", $qry);
        }
        return $qry;
    }

    /**
     * dump the query include the unsecure data passed into sql query string
     */
    public function dumpQuery(){
        echo "<pre>";
        echo $this->getQuery() . PHP_EOL  . PHP_EOL;
        echo $this->getLastSqlQuery() . PHP_EOL  . PHP_EOL;
        print_r($this->_whereStatement->unsecureValue);
        die;
    }
}