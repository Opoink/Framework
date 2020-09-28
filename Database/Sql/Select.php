<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql;

class Select {

    public $query = ["SELECT"];
    
    public $columns = [];
    public $tableName = [];

    /**
     * set the table name where to fetch data from database
     * @param $tableName either string or array
     * return this instance
     */
    public function from($tableName){
        if(is_string($tableName)){
            $this->tableName[] = "`".$tableName."`";
        }
        elseif(is_array($tableName)){
            foreach($tableName as $key => $value){
                $this->tableName[] = "`".$key."` AS `".$value."`";
            }
        }
        return $this;
    }

    /**
     * set the table name where to fetch data from database
     * @param $colNames either string or array
     * return this instance
     */
    public function select($colNames){
        if(is_string($colNames)){
            $this->columns[] = "`".$colNames."`";
        }
        elseif(is_array($colNames)){
            foreach($colNames as $key => $value){
                $_key = str_replace('.', '`.`', $key);
                $this->columns[] = "`".$_key."` AS `".$value."`";
            }
        }
        return $this;
    }

    public function getLastQuery(){
        $query = "SELECT ";
        $query .= $this->getColumns();
        $query .= $this->getFrom();

        echo $query;
        die;
    }

    protected function getColumns(){
        if(count($this->columns) > 0){
            return implode(', ', $this->columns);
        } else {
            return "*";
        }
    }

    protected function getFrom(){
        return " FROM " . implode(', ', $this->tableName);
    }
}