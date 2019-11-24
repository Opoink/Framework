<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*
* this file is used to validate module
* if it is exist, installed, or already created
* 
*/
namespace Of\ModManager;

class Validator {

	/**
	 *	will check if the module was already existing
	 *	and already available for module installation
	 *	return boolean true if exist or false if not
	 */
	public function checkExist($vendor, $module){
		$target = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module;

		if(is_dir($target)){
			$config = $target.DS.'Config.php';
			if(is_file($config)){
				$cfg = include($config);

				if(is_array($cfg) && isset($cfg['version'])){
					return true;
				}
			}
		}
		return false;
	}
}