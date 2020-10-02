<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class Column Extends \Of\Database\Sql\Statements\Statement {
	
	public $columns = [];

    /**
     * tells if the parseValue function if triggered 
     */
    public $isTriggered = false;

    /**
     * parse value of of the columns name
     */
	public function parseValue($colNames){
        $this->isTriggered = true;
		if(is_string($colNames)){
            $this->columns[] = $this->parseStr($colNames);
        }
        elseif(is_array($colNames)){
        	$isAssociative = $this->isAssociative($colNames);

        	if($isAssociative){
        		foreach ($colNames as $key => $value) {
	                $this->columns[] = $this->parseStr($key)." AS `".$value."`";
        		}
        	} else {
	            foreach($colNames as $value){
        			$this->columns[] = $this->parseStr($value);
	            }
        	}
        }
	}

    /**
     * convert the columns array into an sql string 
     * @return string
     */
    public function getColumns(){
        if(!$this->isTriggered){
            return '';
        } else {
            if(count($this->columns) > 0){
                return implode(', ', $this->columns);
            } else {
                return "*";
            }
        }
    }
}
?>