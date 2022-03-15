<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

class ModuleAvailableTables extends \Of\Database\Migration\Migrate {

	/**
	 * return all avaliable database table from all installed modules
	 */
	public function getAllInstalledAvailableStable(){
		$extDir = ROOT.DS.'App'.DS.'Ext'.DS;
		$vendors = $this->_config['modules'];

		$allTables = []; 
		foreach($vendors as $keyVendor => $modules){
			foreach($modules as $module){
				
				$modDir = $extDir . $keyVendor . DS . $module;
				$tableDir = $modDir.DS.'Schema'.DS.'tables';

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

									/**
									 * TODO: check all rows if exist;
									 */
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
}
?>