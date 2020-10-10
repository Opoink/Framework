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
	const TYPES = ['INNER', 'LEFT', 'RIGHT', 'FULL OUTER'];

	/**
	 * holds the string from joins
	 */
	protected $joins = [];

	/**
	 * temporary string for join statement
	 */
	protected $joinTmp = '';

	/**
     * @param $tableName the table name to add in query as join
     * @param $alias string
     * @param $joinType either INNER, LEFT, RIGHT, FULL OUTER
     */
	public function join($tableName, $alias=null, $joinType="INNER"){
		if(in_array($joinType, self::TYPES)) {
			$qry = strtoupper($joinType) . ' JOIN';
			$qry .= ' ' . $this->parseStr($tableName);

			if($alias){
				$qry .= ' AS ' . $this->parseStr($alias);
			}
		}

		$this->joinTmp = $qry;
	}

	public function onJoin($field1, $field2, $condition="="){
		$this->joinHelper(self::OPON, $field1, $field2, $condition);
		$this->joinTmp = '';
	}

	public function andJoin($field1, $field2, $condition="="){
		$lastKey = array_key_last($this->joins);

		$this->joins[$lastKey]['fields'][] = [
			'type' => self::OPAND,
			'field' => $this->parseStr($field1) . ' '.$condition.' ' . $this->parseStr($field2)
		];
	}

	public function orJoin($field1, $field2, $condition="="){
		$lastKey = array_key_last($this->joins);

		$this->joins[$lastKey]['fields'][] = [
			'type' => self::OPOR,
			'field' => $this->parseStr($field1) . ' '.$condition.' ' . $this->parseStr($field2)
		];

		echo "<pre>";
		print_r($this->joins[$lastKey]['fields']);
		die;
	}

	public function joinHelper($operator, $field1, $field2, $condition="="){
		$this->joins[] = [
			'table' => $this->joinTmp,
			'fields' => [
				[
					'type' => self::OPON,
					'field' => $this->parseStr($field1) . ' '.$condition.' ' . $this->parseStr($field2)
				]
			]
		];
	}
}
?>