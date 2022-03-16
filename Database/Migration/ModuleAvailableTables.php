<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

use \Of\Constants;

class ModuleAvailableTables extends \Of\Database\Migration\Migrate {

	protected $_writer;

	public function __construct(
		\Of\Database\Connection $Connection,
		\Of\Database\Entity $Entity,
		\Of\File\Writer $Writer
	){
		parent::__construct($Connection, $Entity);
		$this->_writer = $Writer;
	}


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

	/**
	 * save the database fields to json file
	 * @param $vendor string vendor name
	 * @param $module string module name
	 * @param $tablename string tablename name
	 * @param $field array fields to be saved
	 */
	public function saveFiledsByVendorModuleTablename($vendor, $module, $tablename, $fields, $save_and_install=false){
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';
		
		if(file_exists($targetFile) && is_file($targetFile)){
			$tableContent = file_get_contents($targetFile);
			$tableContent = json_decode($tableContent, true);

			if(json_last_error() == JSON_ERROR_NONE){

				$fieldsToSave = [];

				foreach ($fields as $field) {
					if(!isset($tableContent['fields'])){
						$tableContent['fields'] = [
							$this->setFieldValue($field)
						];
					}
					else {
						foreach ($tableContent['fields'] as $key => &$tableContentField) {
							if($tableContentField['name'] == $field['old_name']){

								if(isset($field['after'])){
									unset($tableContent['fields'][$key]);
									$fieldsToSaveData = $this->setFieldValue($field);
								}
								else {
									$tableContentField = $this->setFieldValue($field);
									$fieldsToSaveData = $tableContentField;
								}

								$fieldsToSaveData['old_name'] = $field['old_name'];
								$fieldsToSave[] = $fieldsToSaveData;
							}
						}
					}
				}

				$tableContent['fields'] = $this->reArrangeFields($tableContent['fields'], $fieldsToSave);

				foreach ($tableContent['fields'] as $key => $field) {
					if(isset($field['primary']) && $field['primary'] == true){
						$tableContent['primary_key'] = $field['name'];
					}
				}

				$tableContent = json_encode($tableContent, JSON_PRETTY_PRINT);

				$this->_writer->setDirPath(dirname($targetFile))
				->setData($tableContent)
				->setFilename($tablename)
				->setFileextension('json')
				->write();

				if($save_and_install){
					$_tableName = $this->_connection->getTablename($tablename);
        			$isExist = $this->fetchTableName($_tableName);
					if(!$isExist){
						$_columns = new Columns();

						$primaryKey = '';
						foreach ($fieldsToSave as $keyColumn => $valueColumn) {
							$_columns->addColumn($valueColumn, $targetFile);

							if (array_key_exists('primary', $valueColumn) && $valueColumn['primary'] == true) {
								$primaryKey = ' , PRIMARY KEY (`'.$valueColumn['name'].'`) ';
							}
						}
						$cols = implode(', ', $_columns->getColumns());
						
						/** in this part the table is not exist so we have to create it */
						$sql = "CREATE TABLE IF NOT EXISTS `".$_tableName."` (".$cols.$primaryKey.")ENGINE=InnoDB DEFAULT CHARSET=utf8;";

						$connection = $this->_connection->getConnection()->getConnection();
						$connection->exec($sql);
					}
					else {
						foreach ($fieldsToSave as $key => $column) {
							$name = $column['name'];
							if(isset($column['old_name'])){
								$name = $column['old_name'];
							}

							$isColumnExist = $this->fetchColumnName($_tableName, $name);

							$_columns = new Columns();
							$_columns->addColumn($column, $targetFile);
							$cols = implode(', ', $_columns->getColumns());

							$sql = '';
							if(!$isColumnExist){
								$sql .= "ALTER TABLE `".$_tableName."` ";
								$sql .= "ADD " . $cols . ";";
							}
							else {
								$sql .= "ALTER TABLE `".$_tableName."` ";
								$sql .= "CHANGE `".$name."` " . $cols . ";";
							}

							try {
								$connection = $this->_connection->getConnection()->getConnection();
								$connection->exec($sql);
							} catch (\PDOException $pe) {
								throw new \Exception("Could not add new column ".$column['name'].": " . $pe->getMessage() . " : " . $sql);
							}
						}
					}
				}
			}
		}
	}

	protected function setFieldValue($newValue){
		$_value = [];

		foreach ($newValue as $key => $value) {
			if($key == 'default'){
				if($value == 'USER_DEFINED'){
					$_value['default'] = $newValue['default_value'];
				}
				else if($value == 'NONE'){
					/** do nothing */
				}
				else if($value == 'NULL'){
					$_value['default'] = null;
					$_value['nullable'] = true;
				}
				else if($value == 'CURRENT_TIMESTAMP'){
					$_value['CURRENT_TIMESTAMP'] = $value;
				}
			}
			else if($key == 'default_value' || $key == 'old_name'){
				continue;
			}
			else {
				if(!empty($value)){
					$_value[$key] = $value;
				}
			}
		}

		return $_value;
	}

	protected function reArrangeFields($tableContentFields, $fieldsToSave){

		foreach ($fieldsToSave as $key => $value) {
			if(isset($value['after'])){
				foreach ($tableContentFields as $_key => $_value) {
					if($value['after'] == $_value['name']){
						array_splice( $tableContentFields, $_key + 1, 0, [$value] );
					}
				}
			}
		}

		return $tableContentFields;
	}
}
?>