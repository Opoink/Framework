<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql;

class Update Extends \Of\Database\Sql\Statements\Statement {

	/** hold the fields for inserting data */
	protected $fields = [];

	/** hold the value for inserting data */
	protected $value = [];

	/** the value for insequre date */
	public $unsecureValue = [];

	/**
     * current value for variable of value sample :a 
     */
    public $valVar = 'a';

	/**
	 * build the key value pair for insert or update value
	 */
	public function prepareData($data){
		foreach ($data as $key => $value) {
			$unsecureKey = ":update".$this->valVar."opoink";

			$this->fields[] = $this->parseStr($key);
			$this->value[] = $unsecureKey;
			$this->unsecureValue[$unsecureKey] = $value;
			$this->valVar++;
		}
        return $this;
	}

	/**
	 * build the insert query 
	 * return qry string
     * @param $select instance of \Of\Database\Sql\Select
	 */
	public function updateQry($tableName, \Of\Database\Sql\Select $select){
		$qry = "UPDATE ".$this->parseStr($tableName)." SET ";

		$fields = [];
		foreach ($this->fields as $key => $field) {
			$fields[] = $field . " = " . $this->value[$key];
		}

		$qry .= implode(', ', $fields);
		$qry .= $select->getQuery();

		foreach ($select->_whereStatement->unsecureValue as $key => $value) {
			$this->unsecureValue[$key] = $value;
		}
        return $qry;
	}
}
?>