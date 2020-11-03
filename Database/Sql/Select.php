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
    public $_joinStatement;

    /**
	 * to make query distinct
     */
    protected $distinct = '';

    /**
     * this tells if the query is a count or not
     */
    protected $count;

    /**
     * hold the value of min and max statement
     * string
     */
    protected $minmax = '';

    /**
     * limit of data to be fetched string ex: LIMIT 10
     */
    public $limit = '';

    /**
     * limit of data to be fetched integere
     */
    public $_limit = 0;

    /**
     * offset of data to be fetched string ex: OFFSET 10
     */
    public $offset = '';

    /**
     * order by
     */
    public $_orderBy = '';

    public function __construct(
        \Of\Database\Sql\Statements\From $From,
        \Of\Database\Sql\Statements\Column $Column,
        \Of\Database\Sql\Statements\Where $Where,
        \Of\Database\Sql\Statements\JoinTable $JoinTable
    ){
        $this->_fromStatement = $From;
        $this->_columnStatement = $Column;
        $this->_whereStatement = $Where;
        $this->_joinStatement = $JoinTable;
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
     * select distinct
     */
    public function distinct(){
    	$this->distinct = 'DISTINCT ';
    	return $this;
    }

    /**
     * select count
     * @param $col the column name to counted
     */
    public function count($col=null, $alias=''){
    	if(!$col){
    		$this->count = ' count(*)';
    	} else {
    		$this->count = ' count(DISTINCT '.$this->_whereStatement->parseStr($col).')';
    	}

    	if(!empty($alias)){
    		$this->count .=  ' AS `'.$alias.'`';
    	}
    	return $this;
    }

    /**
     * max statement
     */ 
    public function max($col, $alias=''){
    	$this->minMax('MAX', $col, $alias);
    	return $this;
    }

    /**
     * min statement
     */ 
    public function min($col, $alias=''){
    	$this->minMax('MIN', $col, $alias);
    	return $this;
    }

    /**
     * set min and max column
     */
    public function minMax($type, $col, $alias=''){
    	$this->minmax = $type."(".$this->_whereStatement->parseStr($col).") ";

    	if(!empty($alias)){
    		$this->count .=  'AS `'.$alias.'` ';
    	}
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
    public function eq($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::EQ, $value, true, $isCol);
        return $this;
    }

    /**
     * add the value not equals condition from previous where statement
     * @param $value string
     */
    public function ne($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::NE, $value, true, $isCol);
        return $this;
    }

    /**
     * add the value less than condition from previous where statement
     * @param $value string
     */
    public function lt($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::LT, $value, true, $isCol);
        return $this;
    }

    /**
     * add the value less than or equals condition from previous where statement
     * @param $value string
     */
    public function ltoe($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::LTOE, $value, true, $isCol);
        return $this;
    }

    /**
     * add the value greater than condition from previous where statement
     * @param $value string
     */
    public function gt($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::GT, $value, true, $isCol);
        return $this;
    }

    /**
     * add the value greater than or equals condition from previous where statement
     * @param $value string
     */
    public function gtoe($value, $isCol=false){
        $this->_whereStatement->addConVal(\Of\Database\Sql\Statements\Where::GTOE, $value, true, $isCol);
        return $this;
    }

    /**
     * add the between from previous where statement
     * @param $from int
     * @param $to int
     * @param $isFromACol boolean either the $from is a column of a table or a int 
     * @param $isToACol boolean either the $to is a column of a table or a int 
     */
    public function between($from, $to, $isFromACol=false, $isToACol=false){
        $this->_whereStatement->between($from, $to, $isFromACol, $isToACol);
        return $this;
    }

    /**
     * add the not between from previous where statement
     * @param $from int
     * @param $to int
     * @param $isFromACol boolean either the $from is a column of a table or a int 
     * @param $isToACol boolean either the $to is a column of a table or a int 
     */
    public function notBetween($from, $to, $isFromACol=false, $isToACol=false){
        $this->_whereStatement->notBetween($from, $to, $isFromACol, $isToACol);
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
     * add limit statement 
     * @param $limit int
     */
    public function limit($_limit){
        $limit = (int)$_limit;
        if($limit){
            $this->limit = ' LIMIT ' . $limit . ' ';
            $this->_limit = $limit;
        }
        return $this;
    }

    /**
     * add offset statement 
     * @param $_offset array
     */
    public function offset($_offset){
        $offset = (int)$_offset;
        $this->offset = ' OFFSET ' . $offset . ' ';
        return $this;
    }

    /**
     * add offset statement 
     * @param $by array || string
     * @param $criterion string ASC or DESC
     */
    public function orderBy($by, $criterion='ASC'){
        $qry = ' ';
        if(empty($this->_orderBy)){
            $qry = ' ORDER BY ';
        }
        if(is_string($by)){
            $qry .= $this->_whereStatement->parseStr($by);
        } else {
            $this->_fromStatement->isAssociative($by);
        }

        $c = strtoupper($criterion);
        if($c == 'ASC' || $c == 'DESC'){
            $qry .= ' ' . $c . ' ';
        } else {
            $qry .= ' ASC ';
        }
        if(!empty($this->_orderBy)){
            $this->_orderBy .= ' , ' . $qry;
        } else {
            $this->_orderBy .= $qry;
        }
        return $this;
    }

    /**
     * @param $tableName the table name to add in query as join
     * @param $alias string
     * @param $joinType either INNER, LEFT, RIGHT, FULL OUTER
     */
    public function join($tableName, $alias=null, $joinType="INNER"){
        $this->_joinStatement->join($tableName, $alias, $joinType);
        return $this;
    }

    public function onJoin($field1, $field2, $condition="="){
        $this->_joinStatement->onJoin($field1, $field2, $condition);
        return $this;
    }

	public function andJoin($field1, $field2, $condition="="){
		$this->_joinStatement->andJoin($field1, $field2, $condition);
		return $this;
	}

	public function orJoin($field1, $field2, $condition="="){
		$this->_joinStatement->orJoin($field1, $field2, $condition);
		return $this;
	}

    public function joinGroup($value){
        if($value instanceof \Closure){
            $this->_joinStatement->joinGroup($value);
        } else {
            throw new \Exception('calling on joinGroup statement of none instanceof \Closure');
        }
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
            
            if($this->count){
            	$query .= $this->count;
            }
            elseif($this->minmax){
            	$query .= $this->minmax;
            } else {
            	$query .= $this->distinct;
	            $query .= $this->_columnStatement->getColumns();
            }

        	$query .= $this->_fromStatement->getFrom();
            $query .= $this->_joinStatement->getJoinQry();
        }
        

        $query .= $this->_whereStatement->getWhere($isSub);
        
        $query .= $this->_orderBy;
        $query .= $this->limit;
        $query .= $this->offset;

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
    }
}