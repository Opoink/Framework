<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql;

class DeleteStatement extends \Of\Database\Sql\Select {

    /**
     * return query query string
     * @param $isSub boolean tells if the query is sub or not
     */
    public function getQuery($isSub=false){
        $query = "DELETE ";
    	$query .= $this->_fromStatement->getFrom();
        $query .= $this->_whereStatement->getWhere($isSub);

        return  $query;
    }
}
?>