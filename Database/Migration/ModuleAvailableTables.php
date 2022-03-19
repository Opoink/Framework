<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

use \Of\Constants;

class ModuleAvailableTables extends \Of\Database\Migration\Migrate {

	const DROP_TABLE_ONLY = 'droptable-only';
	const DELETE_JSON_ONLY = 'delete-json-only';
	const DROP_AND_DELETE_JSON = 'droptable-and-delete-json';

	protected $_writer;

	public function __construct(
		\Of\Database\Connection $Connection,
		\Of\Database\Entity $Entity,
		\Of\Std\Password $Password,
		\Of\File\Writer $Writer
	){
		parent::__construct($Connection, $Entity, $Password);
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
						if($table == '.' || $table == '..' || $this->checkIfFileIsData($table)){
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

				$installDataTarget = dirname($targetFile) . DS . $tablename.'_data.json';
				$tableContent['installation_data'] = null;

				if(file_exists($installDataTarget)){
					$installDataContent = file_get_contents($installDataTarget);
					$installDataContent = json_decode($installDataContent, true);

					if(json_last_error() == JSON_ERROR_NONE){
						$tableContent['installation_data'] = $installDataContent;
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
	public function saveFieldsByVendorModuleTablename($vendor, $module, $tablename, $fields, $save_and_install=false){
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';
		
		if(file_exists($targetFile) && is_file($targetFile)){
			$tableContent = file_get_contents($targetFile);
			$tableContent = json_decode($tableContent, true);

			if(json_last_error() == JSON_ERROR_NONE){

				$fieldsToSave = [];

				foreach ($fields as $field) {

					if(!isset($field['name']) || empty($field['name'])){
						throw new \Exception("The field name is required");
					}

					if(!isset($tableContent['fields'])){
						$tableContent['fields'] = [
							$this->setFieldValue($field)
						];
					}
					else {
						$isUpdate = false;
						foreach ($tableContent['fields'] as $key => &$tableContentField) {
							if($tableContentField['name'] == $field['old_name']){

								if(isset($field['after']) && !empty($field['after'])){
									unset($tableContent['fields'][$key]);
									$fieldsToSaveData = $this->setFieldValue($field);
								}
								else {
									$tableContentField = $this->setFieldValue($field);
									$fieldsToSaveData = $tableContentField;
								}

								$fieldsToSaveData['old_name'] = $field['old_name'];
								$fieldsToSave[] = $fieldsToSaveData;

								$isUpdate = true;
							}
						}

						if(!$isUpdate){
							$fieldsToSaveData = $this->setFieldValue($field);
							$fieldsToSaveData['old_name'] = $fieldsToSaveData['name'];
							$fieldsToSave[] = $fieldsToSaveData;

							/** if has after the filled will e inserted dring the reArrangeFields() method */
							if(!isset($fieldsToSaveData['after'])){
								$tableContent['fields'][] = $fieldsToSaveData;
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

				if($save_and_install){
					$_tableName = $this->_connection->getTablename($tablename);
        			$isExist = $this->fetchTableName($_tableName);
					if(!$isExist){
						$this->createDatabaseTableWithColumns($_tableName, $fieldsToSave);
					}
					else {
						foreach ($fieldsToSave as $key => $column) {
							$this->saveColumnIntoTable($column, $_tableName, $targetFile);
						}
					}
				}

				$tableContent['fields'] = $this->reArrangeFieldsAfter($tableContent['fields']);
				$tableContent = json_encode($tableContent, JSON_PRETTY_PRINT);

				$this->_writer->setDirPath(dirname($targetFile))
				->setData($tableContent)
				->setFilename($tablename)
				->setFileextension('json')
				->write();
			}
		}
	}

	/**
	 * try to drop the field from database
	 * @param $tablename string
	 * @param $fields array
	 */
	public function dropFieldsFromoDatabase($tablename, $fields){
		$_tableName = $this->_connection->getTablename($tablename);
		$isExist = $this->fetchTableName($_tableName);

		$ColumnSuccess = [];
		$ColumnErrors = [];

		if($isExist){
			foreach ($fields as $key => $field) {
				if(isset($field['name']) && !empty($field['name'])){
					$isColumnExist = $this->fetchColumnName($_tableName, $field['name']);
					if($isColumnExist){

						try {
							$connection = $this->_connection->getConnection()->getConnection();
							$connection->exec("ALTER TABLE `".$_tableName."` DROP `".$field['name']."`;");

							$ColumnSuccess[] = "Column ".$field['name']." dropped successfully";
						} catch (\PDOException $pe) {
							$ColumnErrors[] = 'Cannot drop column ' . $field['name'] . ' - ' . $pe->getMessage();
						}
					}
					else {
						$ColumnErrors[] = $field['name'] . ' does not exist from the database';
					}
				}
				else {
					$ColumnErrors[] = $field['name'] . ' is required';
				}
			}
		}
		else {
			throw new \Exception("Database table name ".$_tableName." does not exist");
		}
		return [
			"column_success" => $ColumnSuccess,
			"column_errors" => $ColumnErrors
		];
	}

	/**
	 * this will remove the field from json schema file
	 * @param $vendor string
	 * @param $module string
	 * @param $tablename string
	 * @param $fields array
	 */
	public function removeFieldsFromJsonFile($vendor, $module, $tablename, $fields){
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';

		$success = [];
		$errors = [];

		if(file_exists($targetFile) && is_file($targetFile)){
			$tableContent = file_get_contents($targetFile);
			$tableContent = json_decode($tableContent, true);

			if(json_last_error() == JSON_ERROR_NONE){

				if(isset($tableContent['fields'])){
					foreach ($fields as $key => $field) {
						$isRemoved = false;
						foreach ($tableContent['fields'] as $_key => &$_value) {
							if($_value['name'] == $field['name']){
								unset($tableContent['fields'][$_key]);
								$isRemoved = true;
							}
						}
						if($isRemoved){
							$success[] = $field['name'] . ' successfully removed on your JSON file';
						}
						else {
							$success[] = $field['name'] . ' does not exist on your module JSON file';
						}
					}


					$tableContent['fields'] = $this->reArrangeFieldsAfter($tableContent['fields']);
					$tableContent = json_encode($tableContent, JSON_PRETTY_PRINT);

					$this->_writer->setDirPath(dirname($targetFile))
					->setData($tableContent)
					->setFilename($tablename)
					->setFileextension('json')
					->write();
				}
				else {
					$errors[] = 'There was no field on your JSON files';
				}
			}
			else {
				$errors[] = 'Failed to read JSON file ' . $targetFile;
			}
		}
		else {
			$errors[] = 'File ' . $targetFile . ' does not exist.' ;
		}

		return [
			"success" => $success,
			"errors" => $errors
		];
	}

	/**
	 * crate a JSON file for database table
	 * this mthod will not save it on the database 
	 * just fr the JSON file only
	 */
	public function createTableJsonSchema($vendor, $module, $tableOptions){
		$tablename = $this->cleanName($tableOptions['tablename']);

		if( strtolower($tablename) == 'extension' || strtolower($tablename) == 'system_admin'){
			throw new \Exception('Table name "extension" or "system_admin" is reserved for Opoink use, please use another table name.', 406);
		}

		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';

		if(file_exists($targetFile)){
			throw new \Exception("Database table ".$tablename." already exist", 406);
		}
		else {
			$pk = $tablename . '_id';
			if(isset($tableOptions['primary_key']) && !empty($tableOptions['primary_key'])){
				$pk = $tableOptions['primary_key'];
			}

			$tableInfo = [
				'primary_key' => $pk,
				'fields' => [
					[
						"name" => $pk,
						"type" => "BIGINT",
						"length" => 20,
						"attributes" => "auto_increment",
						"primary" => true
					]
				],
			];

			if(isset($tableOptions['collation']) && !empty($tableOptions['collation'])){
				$tableInfo['collate'] = $tableOptions['collation'];
				$tableInfo['fields'][0]['collation'] = $tableOptions['collation'];
			}
			if(isset($tableOptions['storage_engine']) && !empty($tableOptions['storage_engine'])){
				$tableInfo['engine'] = $tableOptions['storage_engine'];
			}

			$tableContent = json_encode($tableInfo, JSON_PRETTY_PRINT);

			$this->_writer->setDirPath(dirname($targetFile))
			->setData($tableContent)
			->setFilename($tablename)
			->setFileextension('json')
			->write();

			return $this->getAllInstalledAvailableStable();
		}
	}

	protected function cleanName($name){
		$name = str_replace(' ', '_', $name);
		$name = preg_replace("/[^a-zA-Z0-9_]+/", "", $name);
		return $name;
	}

	protected function setFieldValue($newValue){
		$_value = [];
		$newValue['name'] = $this->cleanName($newValue['name']);

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

	/**
	 * insert the field into sepcific possition based on after key
	 */
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

	/**
	 * this will update the after key on each field
	 */
	protected function reArrangeFieldsAfter($tableContentFields){
		foreach ($tableContentFields as $key => &$field) {
			$_prevKey = $key - 1;
			if(isset($tableContentFields[$_prevKey])){
				$field['after'] = $tableContentFields[$_prevKey]['name'];
			}
			if(isset($field['old_name'])){
				unset($field['old_name']);
			}
		}
		return $tableContentFields;
	}

	/**
	 * this is called on \Of\Controller\Sys\SystemDatabaseSavefield
	 * this is in page //domain.com/system<suffix>/database
	 */
	public function _createTable($vendor, $module, $tablename) {
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tablename.'.json';

		if(file_exists($targetFile) && is_file($targetFile)){
			$tableContent = file_get_contents($targetFile);
			$tableContent = json_decode($tableContent, true);

			if(json_last_error() == JSON_ERROR_NONE){
				$this->createTable($tablename, $tableContent, $targetFile, dirname($targetFile), false);
			}
		}
	}

	public function dropTable($vendor, $module, $tableName, $action){
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$tableName.'.json';

		if($action == self::DROP_TABLE_ONLY){
			$this->dropTableFromDatabase($tableName);
			return [
				'message' => 'Database table successfully dropped'
			];
		}
		else if($action == self::DELETE_JSON_ONLY){
			if(file_exists($targetFile)){
				unlink($targetFile);
				return [
					'message' => 'Database table JSON file successfully deleted'
				];
			}
		}
		else if($action == self::DROP_AND_DELETE_JSON){
			$this->dropTableFromDatabase($tableName);
			$message = 'Database table successfully dropped';
			if(file_exists($targetFile)){
				unlink($targetFile);
				$message .= ' and database table JSON file successfully deleted';
			}
			return [
				'message' => $message
			];
		}
		else {
			throw new \Exception("We do not recognize the action requested", 406);
		}
	}

	/**
	 * 
	 */
	public function createInstallData($vendor, $module, $tableName, $fields, $saveToDatabase=false){
		$tableName = $this->cleanName($tableName);
		$fileName = $tableName.'_data';
		
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_TABLES_DIR.DS.$fileName.'.json';
		
		$dataContent = [];
		if(file_exists($targetFile)){
			$dataContent = file_get_contents($targetFile);
			$dataContent = json_decode($dataContent, true);

			if(json_last_error() == JSON_ERROR_NONE){
				$dataContent = $dataContent;
			}
		}

		foreach ($fields as $key => $field) {
			if(isset($field['value']) && empty($field['value'])){
				unset($fields[$key]);
			}
		}

		$dataContent[] = $fields;

		$dataContent = json_encode($dataContent, JSON_PRETTY_PRINT);

		$this->_writer->setDirPath(dirname($targetFile))
		->setData($dataContent)
		->setFilename($fileName)
		->setFileextension('json')
		->write();

		return $dataContent;
	}
}
?>