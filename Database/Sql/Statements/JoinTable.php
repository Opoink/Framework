<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class JoinTable Extends \Of\Database\Sql\Statements\Statement {

	const OPON = 'ON';
	const OPAND = 'AND';
	const OPOR = 'OR';
	const TYPES = ['INNER', 'LEFT', 'RIGHT', 'LEFT OUTER', 'RIGHT OUTER'];

	/**
	 * holds the string from joins
	 */
	public $joins = [];

	/**
	 * temporary string for join statement
	 */
	protected $joinTmp = '';

	/**
	 * unsecure value for join will be added here
	 */
	public $unsecureValue = [];

	/**
     * current value for variable of value sample :a 
     */
    public $valVar = 'a';

    /**
     * prefix that will be appended on query for insecure data
     */
    public $valPrefix = 'join_';

    /**
     * return the new unsecure key
     */
    public function getUnsecureKey(){
    	$key = ":".$this->valPrefix.$this->valVar.'opoink';
    	$this->valVar++;
    	return $key;
    }

    /**
     * set the unsecure value
     * return string unsecure key
     * @param $value string
     */
    public function setUnsecureValue($value){
    	$key = $this->getUnsecureKey();
		$this->unsecureValue[$key] = $value;
		return $key;
    }

	/**
     * @param $tableName the table name to add in query as join
     * @param $alias string
     * @param $joinType either INNER, LEFT, RIGHT, 'LEFT OUTER', 'RIGHT OUTER'
     */
	public function join($tableName, $alias=null, $joinType="INNER"){
		$qry = 'INNER JOIN';
		if(in_array($joinType, self::TYPES)) {
			$qry = strtoupper($joinType) . ' JOIN';
		}
		
		$qry .= ' ' . $this->parseStr($tableName);
		if($alias){
			$qry .= ' AS ' . $this->parseStr($alias);
		}
		$this->joinTmp = $qry;
		return $this;
	}

	public function onJoin($field1, $field2, $condition="=", $field2isString=false){
		if(!$field2isString){
			$f2 = $this->parseStr($field2);
		} else {
			$f2 = $this->setUnsecureValue($field2);
		}
		$this->joins[] = [
			'table' => $this->joinTmp,
			'fields' => [
				[
					'type' => self::OPON,
					'field' => $this->parseStr($field1) . ' '.$condition.' '.$f2
				]
			]
		];

		$this->joinTmp = '';
		return $this;
	}

	public function andJoin($field1, $field2, $condition="=", $field2isString=false){
		if(!$field2isString){
			$f2 = $this->parseStr($field2);
		} else {
			$f2 = $this->setUnsecureValue($field2);
		}

		$lastKey = array_key_last($this->joins);

		$this->joins[$lastKey]['fields'][] = [
			'type' => self::OPAND,
			'field' => $this->parseStr($field1) . ' '.$condition.' ' . $f2
		];
		return $this;
	}

	public function orJoin($field1, $field2, $condition="=", $field2isString=false){
		if(!$field2isString){
			$f2 = $this->parseStr($field2);
		} else {
			$f2 = $this->setUnsecureValue($field2);
		}

		$lastKey = array_key_last($this->joins);

		$this->joins[$lastKey]['fields'][] = [
			'type' => self::OPOR,
			'field' => $this->parseStr($field1) . ' '.$condition.' ' . $f2
		];
		return $this;
	}

	public function joinGroup($values){
		if($values instanceof \Closure){
         	if($this->valPrefix == 'join_'){
	            $this->valPrefix = 'join_a';
	        } else {
	            $this->valPrefix++;
	        }
			$di = new \Of\Std\Di();
        	$subJoin = $di->get('\Of\Database\Sql\Statements\JoinTable');
	        $subJoin->valPrefix = $this->valPrefix;
	        $this->valPrefix++;
	        $this->valPrefix++;

            $values($subJoin);

           	foreach ($subJoin->unsecureValue as $key => $value) {
           		$this->unsecureValue[$key] = $value;
           	}

            $lastKey = array_key_last($this->joins);
            
           	if(!is_integer($lastKey)){
           		$this->joins[] = [
					'table' => $this->joinTmp,
					'fields' => [
						[
							'type' => self::OPON,
							'field' => $subJoin
						]
					]
				];
           	} else {
				$this->joins[$lastKey]['fields'][] = [
					'type' => self::OPAND,
					'field' => $subJoin
				]; 
           	}
		}
	}

	/**
	 * this method was not been used
	 */
	/*public function joinHelper($operator, $field1, $field2, $condition="=", $field2isString=false){
		$f2 = "'".$field2."'";
		if(!$field2isString){
			$f2 = $this->parseStr($field2);
		}
		$this->joins[] = [
			'table' => $this->joinTmp,
			'fields' => [
				[
					'type' => self::OPON,
					'field' => $this->parseStr($field1) . ' '.$condition.' ' . $f2
				]
			]
		];
	}*/

	public function getJoinQry($isSub=false){
		$qry = "";
		foreach ($this->joins as $key => $value) {
			if(isset($value['table'])){
				$qry .= ' ' . $value['table'] . ' ';
			}

			foreach ($value['fields'] as $fkey => $field) {	
				if($field['field'] instanceof \Of\Database\Sql\Statements\JoinTable){
					$qry .= ' ' . $field['type'] . ' ( ' . $field['field']->getJoinQry(true) . ')';
				} else {
					if($isSub){
						if($fkey > 0){
							$qry .= $field['type'] . ' ' . $field['field'];
						} else {
							$qry .= $field['field'];
						}
					} else {
						$qry .= $field['type'] . ' ' . $field['field'];
					}
				}
				$qry .= ' ';
			}
		}
		return $qry;
	}
}
?>