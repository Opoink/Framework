<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Sql\Statements;

class Statement {

	/**
	 * check the if the array is associstive or not
	 * @param $arr 
	 * return boolean
	 */
	public function isAssociative($arr) {
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * parse the string for columns name 
	 */
	public function parseStr($str){
		$strArray = explode('.', $str);

		$_str = '`' . $strArray[0] . '`';

		if(isset($strArray[1])){
			if($strArray[1] == '*'){
				$_str .= '.*';
			} else {
				$_str .= '.`' . $strArray[1] . '`';
			}
		}
		return $_str;
	}
}
?>