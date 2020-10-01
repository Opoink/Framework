<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class Where Extends \Of\Database\Sql\Statements\Statement {
	
	const WHERE = 'WHERE';

    public $where = [];

	public function where($where, $condition, $value) {
		$qry = $this->parseStr($where) . ' ' . $condition . " '" . $value . "'";
		$this->addWhere($qry);
		var_dump($this->where);
    }

    public function addWhere($qry){
    	if(!count($this->where)){
    		$this->where[self::WHERE] = $qry;
    	} else {
    		// $lastKey = array_key_last($this->where);
    		// if($lastKey == self::WHERE){

    		// }
    		// var_dump();
    	}
    }
}
?>