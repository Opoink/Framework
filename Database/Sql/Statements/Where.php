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

    public $where = [];

    /**
     * temporary string for where while waiting for 
     * its condition and value
     * where function will reset this variable
     */
    private $whereTmp = '';

    /**
     * this will hold the value that was set into condition functions
     */
    private $unsecureValue = [];

    /**
     * set the where tempory string before it goes into
     * where array
     * @param $where string the field to be conditioned
     */
	public function where($where) {
        $this->whereTmp = $this->parseStr($where) . ' ';
    }

    /**
     * add where condition for the query
     * if $where is an empty array means it is the start 
     * so it will add WHERE operator, else add AND operator instead
     * @param $qry string the field to be conditioned
     */
    public function addWhere($qry){
    	if(!count($this->where)){
    		$this->where[] = [
                'operator' => self::OPWHERE,
                'qry' => $qry
            ];
    	} else {
            $this->where[] = [
                'operator' => self::OPAND,
                'qry' => $qry
            ];
    	}
    }

    /**
     * add where condition for the query
     * if $where is an empty array means it is the start 
     * so it will add WHERE operator, else add AND operator instead
     * @param $condition string
     * @param $value string 
     */
    public function addConVal($condition, $value){
        if(!empty($this->whereTmp)){
            $this->whereTmp .= $condition . ' ?';
            $this->addWhere($this->whereTmp);
            $this->unsecureValue[] = $value;
        }
    }

    /**
     * add the between from previous where statement
     * @param $from int
     * @param $to int
     */
    public function between($from, $to){
        $this->betweenOrNotBetween($from, $to, true);
    }

    /**
     * add the not between from previous where statement
     * @param $from int
     * @param $to int
     */
    public function notBetween($from, $to){
        $this->betweenOrNotBetween($from, $to, false);
    }

    /**
     * add the between ot notBetween from previous where statement
     * @param $from int
     * @param $to int
     */
    public function betweenOrNotBetween($from, $to, $between = true){
        if(!empty($this->whereTmp)){
            $not = 'NOT ';
            if($between){
                $not = '';
            }
            $this->whereTmp .= $not . 'BETWEEN ? AND ?';
            $this->addWhere($this->whereTmp);
            $this->unsecureValue[] = $from;
            $this->unsecureValue[] = $to;
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

            $qry = $not . "IN (";

            $vals = [];
            foreach ($values as $key => $value) {
                $vals[] = '"?"';
                $this->unsecureValue[] = $value;
            }

            $qry .= implode(', ', $vals);
            $qry .= ")";
            $this->whereTmp .= $qry;
            $this->addWhere($this->whereTmp);
        }
    }

    // public function orWhere($where, $condition, $value) {
    //     $qry = $this->parseStr($where) . ' ' . $condition . " '" . $value . "'";
    //     $this->where[self::OPOR] = $qry;
    // }

    public function getWhere(){
        // $qry = "";
        // foreach ($this->where as $key => $value) {
        //     $qry .= " " . $key . " " . $value . "";
        // }
        // return $qry;
        var_dump($this->where);
        var_dump($this->unsecureValue);
    }
}
?>