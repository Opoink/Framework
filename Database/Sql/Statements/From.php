<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class From Extends \Of\Database\Sql\Statements\Statement {
	
	public $tableName = [];

    /**
     * tells if the parseValue function if triggered 
     */
    public $isTriggered = false;

	public function parseValue($tableName){
        $this->isTriggered = true;
		if(is_string($tableName)){
            $this->tableName[] = "`".$tableName."`";
        }
        elseif(is_array($tableName)){
        	$isAssociative = $this->isAssociative($tableName);

        	if($isAssociative){
        		foreach ($tableName as $key => $value) {
	                $this->tableName[] = "`".$key."` AS `".$value."`";
        		}
        	} else {
	            foreach($tableName as $key => $value){
        			$this->tableName[] = "`".$value."`";
	            }
        	}
        }
	}

	public function getFrom(){
        if($this->isTriggered){
            return " FROM " . implode(', ', $this->tableName);
        } else {
            return '';
        }
    }
}
?>