<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql;

class Insert Extends \Of\Database\Sql\Statements\Statement {

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
			$unsecureKey = ":".$this->valVar."opoink";

			$this->fields[] = $this->parseStr($key);
			$this->value[] = $unsecureKey;
			$this->unsecureValue[$unsecureKey] = $value;
			$this->valVar++;
		}
        return $this;
	}

	public function insert($tableName){
		$qry = "INSERT INTO " . $this->parseStr($tableName);
		$qry .= "(". implode(', ', $this->fields) .")";
		$qry .= " VALUES ";
		$qry .= "(". implode(', ', $this->value) .")";
        return $qry;
	}
}
?>