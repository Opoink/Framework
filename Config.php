<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of;

class Config {
	
	protected $config;
	
	public function __construct(){
		$config = ROOT.DS.'etc'.DS.'Config.php';
		if(file_exists($config)){
			$this->config = include($config);
		}
		else {
			$this->config = [];
		}
	}
	
	public function getConfig($keys=null){
		if($keys){
			return opoinkGetArrayValue($keys, $this->config);
			// if(isset($this->config[$keys])){
			// 	return $this->config[$keys];
			// }
		} else {
			return $this->config;
		}
	}

	/**
	 * @param $keys path formated string 
	 * sample: product/quantity_and_stock_status/qty
	 */
	// public function getValue($keys, $haystack){
	// 	$keys = explode('/', $keys);

	// 	$valResult = $haystack;
	// 	foreach ($keys as $key => $value) {
	// 		if(isset($valResult[$value])){
	// 			$valResult = $valResult[$value];
	// 			if(!is_array($valResult)){
	// 				break;
	// 			}
	// 		}
	// 		else {
	// 			$valResult = null;
	// 			break;
	// 		}
	// 	}
	// 	return $valResult;
	// }
}

?>