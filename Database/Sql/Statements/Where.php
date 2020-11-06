<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class Where Extends \Of\Database\Sql\Statements\Statement {
	
    const OPWHERE = 'WHERE';
    const OPAND = 'AND';
	const OPOR = 'OR';

    const EQ = '=';
    const NE = '!=';
    const LT = '<';
    const LTOE = '<=';
    const GT = '>';
    const GTOE = '>=';
    const LIKE = 'LIKE';
    const NOTLIKE = 'NOT LIKE';
    const ISNULL = 'IS NULL';
    const ISNOTNULL = 'IS NOT NULL';

    public $where = [];

    /**
     * prefix that will be appended on query for insecure data
     */
    public $valPrefix = '';

    /**
     * current value for variable of value sample :a 
     */
    public $valVar = 'a';

    /**
     * temporary string for where while waiting for 
     * its condition and value
     * where function will reset this variable
     */
    private $whereTmp = '';

    /**
     * temporary string for where while waiting for 
     * its condition and value
     * where function will reset this variable
     */
    private $orWhereTmp = '';

    /**
     * this will hold the value that was set into condition functions
     */
    public $unsecureValue = [];

    /**
     * set the where tempory string before it goes into
     * where array
     * @param $where string the field to be conditioned
     */
	public function where($where) {
        $this->whereTmp = $this->parseStr($where) . ' ';
    }

    /**
     * find which where will be used either 
     * whereTmp or orWhereTmp
     * return string
     */
    private function getWhereOrWhereTmp(){
        $tmp = '';
        if(!empty($this->whereTmp) && empty($this->orWhereTmp)){
            $tmp = $this->whereTmp;
        }
        elseif(empty($this->whereTmp) && !empty($this->orWhereTmp)){
            $tmp = $this->orWhereTmp;
        }
        return $tmp;
    }

    /**
     * return the operator to be used in SQL WHERE Clause 
     */
    private function getOperator(){
        $operator = self::OPAND;
        if(!empty($this->whereTmp) && empty($this->orWhereTmp)){
            if(!count($this->where)){
                $operator = self::OPWHERE;
            }
        }
        elseif(empty($this->whereTmp) && empty(!$this->orWhereTmp)){
            $operator = self::OPOR;
        }

        return $operator;
    }

    /**
     * add where condition for the query
     * if $where is an empty array means it is the start 
     * so it will add WHERE operator, else add AND operator instead
     * @param $qry string the field to be conditioned
     */
    public function addWhere($qry, $subquery=null, $addOperator=true){
        $where = [
            'operator' => $addOperator ? $this->getOperator() : '',
            'qry' => $qry
        ];

        if($subquery instanceof \Of\Database\Sql\Select){
            $where['subquery'] = $subquery;
        }

        $this->where[] = $where;

        $this->whereTmp = '';
        $this->orWhereTmp = '';
    }

    /**
     * add where condition for the query
     * if $where is an empty array means it is the start 
     * so it will add WHERE operator, else add AND operator instead
     * @param $condition string
     * @param $value string 
     * @param $isCol boolean either the condition is a column of a table or a string 
     */
    public function addConVal($condition, $value, $addOperator=true, $isCol=false){
        $tmp = $this->getWhereOrWhereTmp();

        if(!empty($tmp)){
            if($value instanceof \Closure){
                $subquery = $this->getSubSelect();
                $value($subquery);
                $tmp .= $condition . ' ';
                $this->addWhere($tmp, $subquery, $addOperator);
                $this->valVar++;
            } else {
                if(!$isCol){
                    $insecureDataKey = ':'.$this->valPrefix.$this->valVar.'opoink';
                    $tmp .= $condition . ' ' . $insecureDataKey;
                    $this->addWhere($tmp);
                    $this->unsecureValue[$insecureDataKey] = $value;
                    $this->valVar++;
                } else {
                    $tmp .= $condition . ' ' . $this->parseStr($value);
                    $this->addWhere($tmp);
                }
            }
        }
    }

    /**
     * retun new instance of \Of\Database\Sql\Select
     */ 
    private function getSubSelect(){
        $di = new \Of\Std\Di();
        $subquery = $di->get('\Of\Database\Sql\Select');
        /*$isEmpty = true;*/
        if($this->valPrefix == ''){
            $this->valPrefix = 'a';
        } else {
            /*$isEmpty = false;*/
            $this->valPrefix++;
        }
        $subquery->_whereStatement->valPrefix = $this->valPrefix;
        $this->valPrefix++;
        $this->valPrefix++;
        /*if($isEmpty){
            $this->valPrefix = '';
        } else {
            $this->valPrefix--;
        }*/
        return $subquery;
    }

    /**
     * add the between from previous where statement
     * @param $from int
     * @param $to int
     * @param $isFromACol boolean either the $from is a column of a table or a int 
     * @param $isToACol boolean either the $to is a column of a table or a int 
     */
    public function between($from, $to, $isFromACol=false, $isToACol=false){
        $this->betweenOrNotBetween($from, $to, true, $isFromACol, $isToACol);
    }

    /**
     * add the not between from previous where statement
     * @param $from int
     * @param $to int
     * @param $isFromACol boolean either the $from is a column of a table or a int 
     * @param $isToACol boolean either the $to is a column of a table or a int 
     */
    public function notBetween($from, $to, $isFromACol=false, $isToACol=false){
        $this->betweenOrNotBetween($from, $to, false, $isFromACol, $isToACol);
    }

    /**
     * add the between ot notBetween from previous where statement
     * @param $from int
     * @param $to int
     */
    public function betweenOrNotBetween($from, $to, $between = true, $isFromACol=false, $isToACol=false){
        if(!empty($this->whereTmp)){
            $not = 'NOT ';
            if($between){
                $not = '';
            }

            if(!$isFromACol){
                $insecureDataKey = ':'.$this->valPrefix.$this->valVar.'opoink';
                $this->whereTmp .= $not . 'BETWEEN '.$insecureDataKey.' AND ';
                $this->unsecureValue[$insecureDataKey] = $from;
                $this->valVar++;
            } else {
                $this->whereTmp .= $not . 'BETWEEN '.$this->parseStr($from).' AND ';
            }

            if(!$isToACol){
                $insecureDataKey = ':'.$this->valPrefix.$this->valVar.':';
                $this->whereTmp .= $insecureDataKey;
                $this->unsecureValue[$insecureDataKey] = $to;
                $this->valVar++;
            } else {
                $this->whereTmp .= $this->parseStr($to);
            }

            $this->addWhere($this->whereTmp);
        }
    }

    /**
     * add the in statement from previous where statement
      * @param $values array
     */
    public function in($values){
        $this->inOrNotIn($values, true);
    }

    /**
     * add the in statement from previous where statement
      * @param $values array
     */
    public function notIn($values){
        $this->inOrNotIn($values, false);
    }

    public function inOrNotIn($values, $in = true){
        if(!empty($this->whereTmp)){
            $not = 'NOT ';
            if($in){
                $not = '';
            }

            if($values instanceof \Closure){
                $subquery = $this->getSubSelect();
                $values($subquery);

                $qry = $not . " IN (" . $subquery->getQuery() . ") ";
                $this->whereTmp .= $qry;
                $this->addWhere($this->whereTmp);
                foreach ($subquery->_whereStatement->unsecureValue as $key => $value) {
                    $this->unsecureValue[$key] = $value;
                }
                $this->valVar++;
            } else {
                $qry = $not . "IN (";
                $vals = [];
                foreach ($values as $key => $value) {
                    $insecureDataKey = ':'.$this->valPrefix.$this->valVar.'opoink';
                    $vals[] = $insecureDataKey;
                    $this->unsecureValue[$insecureDataKey] = $value;
                    $this->valVar++;
                }

                $qry .= implode(', ', $vals);
                $qry .= ")";
                $this->whereTmp .= $qry;
                $this->addWhere($this->whereTmp);
            }
        }
    }

    public function isNull($isNull){
        if($isNull == true){
            $this->whereTmp .= self::ISNULL;
        } else {
            $this->whereTmp .= self::ISNOTNULL;
        }
        $this->addWhere($this->whereTmp);
    }

    public function orWhere($orWhere) {
        $this->orWhereTmp = $this->parseStr($orWhere) . ' ';
    }

    public function getWhere($isSub=false){
        $qry = "";
        foreach ($this->where as $key => $value) {
            if(isset($value['subquery'])){
                if($value['subquery'] instanceof \Of\Database\Sql\Select){
                   
                    $qry .= " " . $value['operator'];

                    if($value['subquery']->_columnStatement->isTriggered && $value['subquery']->_fromStatement->isTriggered){
                        $qry .= " " . $value['qry'] . "(" . $value['subquery']->getQuery() . ")";
                    } else {
                        $qry .= " (" . $value['subquery']->getQuery(true) . ") ";
                    }
                    foreach ($value['subquery']->_whereStatement->unsecureValue as $key => $value) {
                        $this->unsecureValue[$key] = $value;
                    }
                }
            } else {
                if($isSub && $key < 1){
                    $qry .= " " . $value['qry'] . " ";
                } else {
                    $qry .= " " . $value['operator'] . " " . $value['qry'] . " ";
                }
            }
        }
        return $qry;
    }
}
?>