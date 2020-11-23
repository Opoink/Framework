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

    public $_whereStatement = null;

    /**
     * this will be the efault alias is the colname is a closure
     */
    public $closureInstanceAlias = 'col_a';

    public $noAsterisk = false;

    /**
     * retun new instance of \Of\Database\Sql\Select
     */ 
    private function getSubSelect(){
        return $this->_whereStatement->getSubSelect();
    }

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
                    if($value instanceof \Closure){
                        $this->closureInstanceAlias++;
                        $this->parseValueClosureInstance($value, $key);
                    } else {
                        $this->columns[] = $this->parseStr($key)." AS `".$value."`";
                    }
        		}
        	} else {
	            foreach($colNames as $value){
                    if($value instanceof \Closure){
                        $this->closureInstanceAlias++;
                        $this->parseValueClosureInstance($value, $this->closureInstanceAlias);
                    } else {
                        $this->columns[] = $this->parseStr($value);
                    }
	            }
        	}
        }
        elseif($colNames instanceof \Closure){
            $this->closureInstanceAlias++;
            $this->parseValueClosureInstance($colNames, $this->closureInstanceAlias);
        }
	}

    protected function parseValueClosureInstance($value, $alias){
        $subquery = $this->getSubSelect();
        $value($subquery);
        $this->columns[] = '('.$subquery->getQuery().') AS `' . $alias . '`';

        foreach ($subquery->_whereStatement->unsecureValue as $key => $value) {
            $this->_whereStatement->unsecureValue[$key] = $value;
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
                if(!$this->noAsterisk){
                    return "*";
                }
            }
        }
    }
}
?>