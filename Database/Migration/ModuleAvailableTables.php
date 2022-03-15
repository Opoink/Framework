<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

use \Of\Constants;

class ModuleAvailableTables extends \Of\Database\Migration\Migrate {

	/**
	 * return all avaliable database table from all installed modules
	 */
	public function getAllInstalledAvailableStable(){
		$extDir = Constants::EXT_DIR.DS;
		$vendors = $this->_config['modules'];

		$allTables = []; 
		foreach($vendors as $keyVendor => $modules){
			foreach($modules as $module){
				
				$modDir = $extDir . $keyVendor . DS . $module;
				$tableDir = $modDir.Constants::MODULE_DB_TABLES_DIR;

				$extName = $keyVendor . '_' . $module;
				$allTables[$extName] = [];

				if(is_dir($tableDir)){
					$files = scandir($tableDir);
					foreach ($files as $key => $table) {
						if($table == '.' || $table == '..'){
							continue;
						}
						$targetFile = $tableDir . DS . $table;
						try {
							$tableContent = file_get_contents($targetFile);
							$tableContent = json_decode($tableContent, true);

							if(json_last_error() == JSON_ERROR_NONE){
								$tableName = $this->getTableNameFromFileName($targetFile);
								$dbTableName = $this->_connection->getTablename($tableName);

								$isExist = $this->fetchTableName($dbTableName);
								$tableContent['is_installed'] = false;
								if($isExist){
									$tableContent['is_installed'] = true;
								}
 
								$tableContent['prefixed_tablename'] = $dbTableName;
								$allTables[$extName][$tableName] = $tableContent;
							}
						}
						catch (\Exception $e) {
							/** do nothing */
						} 
					}
				}
			}
		}

		return $allTables;
	}


	/**
	 * return fields by module name and table name
	 */
	public function getFieldsByVendorModuleAndTableName($vendor, $module, $tablename){

		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';

		if(file_exists($targetFile) && is_file($targetFile)){
			$tableContent = file_get_contents($targetFile);
			$tableContent = json_decode($tableContent, true);

			if(json_last_error() == JSON_ERROR_NONE){
				$tableName = $this->getTableNameFromFileName($targetFile);
				$dbTableName = $this->_connection->getTablename($tableName);

				$isExist = $this->fetchTableName($dbTableName);
				$tableContent['is_installed'] = false;
				if($isExist){
					$tableContent['is_installed'] = true;
				}

				$tableContent['prefixed_tablename'] = $dbTableName;

				$fields = $tableContent['fields'];
				foreach ($fields as $key => $field) {
					$tableContent['fields'][$key]['is_installed'] = false;
					$isExist = $this->fetchColumnName($dbTableName, $field['name']);

					if($isExist){
						$tableContent['fields'][$key]['is_installed'] = true;
					}

				}

				return $tableContent;
			}
		}
	}
}
?>