<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

use \Of\Constants;
use \Of\Database\Migration\Columns;

class Migrate {

    protected $vendorName;
    protected $moduleName;
    protected $_config;
    protected $_di;
    protected $_password;

    /**
     * instance of \Of\Database\Connection
     */
    protected $_connection;
    protected $_entity;
    
    public function __construct(
        \Of\Database\Connection $Connection,
        \Of\Database\Entity $Entity,
		\Of\Std\Password $Password
    ){
        $this->_connection = $Connection;
        $this->_entity = $Entity;
        $this->_password = $Password;
    }

    public function setDi($di) {
        $this->_di = $di;
        return $this;
    }

    /**
     * set the vendor name
     */
    public function setVendorName($vendorName){
        $this->vendorName = $vendorName;
        return $this;
    }

    /**
     * set the module name
     */
    public function setModuleName($moduleName){
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * set the system config
     */
    public function setConfig($Config){
        $this->_config = $Config;
        return $this;
    }

    /**
     * initialize the migration for the current vendor_module
     */
    public function init($isSaveData=false){
        $targetDir = ROOT . DS . 'App' . DS . 'Ext' . DS . $this->vendorName . DS . $this->moduleName . DS . 'Schema' . DS . 'tables';

        $installedTableNames = [];
        if(is_dir($targetDir)){
            $files = $this->getDirFiles($targetDir);
            if($files){
                foreach ($files as $key => $file) {
					if($this->checkIfFileIsData($file)){
						continue;
					}
                    $_file = $targetDir.DS.$file;
                    if(file_exists($_file)){
                        $tableName = $this->getTableNameFromFileName($_file);
                        if(!$tableName){
                            throw new \Exception("Invalid JSON schema: " . $_file, 406);
                        }

                        $fields = json_decode(file_get_contents($_file), true);
                        if(json_last_error() == JSON_ERROR_NONE){ /** to make sure that the json file was no error */
                            $installedTableNames[] = $this->createTable($tableName, $fields, $_file, $targetDir, $isSaveData);
                        } else {
                            throw new \Exception("Invalid JSON schema: " . json_last_error_msg() . ' --- ' . $_file, 406);
                        }
                    }
                }
            }
        }
        return $installedTableNames;
    }

    /**
     * since we assume that all files are in JSON format 
     * and the filename is actually the table of the database
     * we will try to extract the filename and clean the string
     * to get a valid table name
     */
    protected function getTableNameFromFileName($file){
        $info = pathinfo($file);

        $name = null;
        if(isset($info['filename'])){
            $name = preg_replace("/[^A-Za-z0-9 ]/", '_', $info['filename']);
            $name = strtolower($name);
        }
        return $name;
    }

	/**
	 * this will create database table including all column 
	 * in the JSON file
	 */
	protected function createDatabaseTableWithColumns($tableName, $columns, $filePath){
		$_columns = new Columns();

		$primaryKey = '';
		foreach ($columns as $keyColumn => $valueColumn) {
			/** 
			 * since this is new table creation must not be here 
			 * so we have to unset after if it is declared in the JSON file
			 */
			if(isset($valueColumn['after'])){
				unset($valueColumn['after']);
			}
			$_columns->addColumn($valueColumn, $filePath);

			if (array_key_exists('primary', $valueColumn) && $valueColumn['primary'] == true) {
				$primaryKey = ' , PRIMARY KEY (`'.$valueColumn['name'].'`) ';
			}
		}
		$cols = implode(', ', $_columns->getColumns());
		
		$collate = "COLLATE='utf8_general_ci'";
		$charset = 'DEFAULT CHARSET=utf8';

		if(isset($tableContent['collate']) && !empty($tableContent['collate'])){
			$collate = "COLLATE='".$tableContent['collate']."'";
			$charset = explode('_', $tableContent['collate']);
			$charset = 'DEFAULT CHARSET='.$charset[0];
		}

		$engine = 'ENGINE=InnoDB';
		if(isset($tableContent['engine']) && !empty($tableContent['engine'])){
			$engine = "ENGINE='".$tableContent['engine']."'";
		}
		$sql = "CREATE TABLE IF NOT EXISTS `".$tableName."` (".$cols.$primaryKey.")".$engine." ".$charset." ".$collate.";";

		try {
			$connection = $this->_connection->getConnection()->getConnection();
			$connection->exec($sql);
			return true;
		} catch (\PDOException $pe) {
			throw new \Exception("Failed to create a new table: " . $pe->getMessage() . " : " . $sql);
		}
	}

	/**
	 * save the column into an existing table
	 * @param $column array the column info
	 * @param $tableName string
	 * @param $filePath string the absolute path of JSON file
	 */
	public function saveColumnIntoTable($column, $tableName, $filePath, $prevColumn=null){
		$name = $column['name'];
		if(isset($column['old_name'])){
			$name = $column['old_name'];
		}

		$isColumnExist = $this->fetchColumnName($tableName, $name);

		$_columns = new Columns();
		$_columns->addColumn($column, $filePath, $prevColumn);
		$cols = implode(', ', $_columns->getColumns());

		$sql = '';
		if(!$isColumnExist){
			$sql .= "ALTER TABLE `".$tableName."` ADD " . $cols . ";";
		}
		else {
			$sql .= "ALTER TABLE `".$tableName."` CHANGE `".$name."` " . $cols . ";";
		}

		try {
			$connection = $this->_connection->getConnection()->getConnection();
			$connection->exec($sql);
		} catch (\PDOException $pe) {
			throw new \Exception("Could not add new column ".$column['name'].": " . $pe->getMessage() . " : " . $sql);
		}
	}

	/**
	 * this will try to drop table
	 * @param $tableName string
	 * return true || throw an error
	 */
	protected function dropTableFromDatabase($tableName){
		$tableName = trim($tableName);
		$tableName = $this->_connection->getTablename($tableName);
        $isExist = $this->fetchTableName($tableName);
		if($isExist){
			$sql = "DROP TABLE `".$tableName."`;";		
			try {
				$connection = $this->_connection->getConnection()->getConnection();
				$connection->exec($sql);
				return true;
			} catch (\PDOException $pe) {
				throw new \Exception("Cannot drop table ".$tableName.": " . $pe->getMessage() . " : " . $sql, 500);
			}
		}
		else {
			return true;
		}
	}

    /**
     * create the table but to ensure the it is not already installed 
     * we will check it inside the information schema
     */
    protected function createTable($tableName, $fields, $_file, $targetDir, $saveData=true){

        $databaseName = $this->_connection->getConfig('database');
        $tableName = $this->_connection->getTablename($tableName);

        $isExist = $this->fetchTableName($tableName);
 
        $columns = [];
        if(isset($fields['fields']) ){
            $columns = $fields['fields'];
        }
        if(!$isExist) {
            if(count($columns)){
				$this->createDatabaseTableWithColumns($tableName, $columns, $_file);
                $_GET['module_install_result'][] = [
                    'message' => $tableName.': Database created.',
                ];
            }
        } else {
            /** 
             * the table was already exisitng here
             * so we have to check each field if existing or not 
             * if the field is not exist we have to do altering the table
             */
            $prevColumn = null;
            foreach ($columns as $key => $column) {
                if(isset($column['name'])){
                    $isColumnExist = $this->fetchColumnName($tableName, $column['name']);
                    if(!$isColumnExist){
						$this->saveColumnIntoTable($column, $tableName, $_file, $prevColumn);

                        // $_columns = new Columns();
                        // $_columns->addColumn($column, $_file, $prevColumn);
                        // $cols = implode(', ', $_columns->getColumns());

                        // $sql = "ALTER TABLE `".$tableName."` ";
                        // $sql .= "ADD " . $cols . ";";

                        // try {
                        //     $connection = $this->_connection->getConnection()->getConnection();
                        //     $connection->exec($sql);
                        // } catch (\PDOException $pe) {
                        //     throw new \Exception("Could not add new column ".$column['name'].": " . $pe->getMessage() . " : " . $sql);
                        // }

                        $_GET['module_install_result'][] = [
                            'message' => $column['name'].': added into '.$tableName.' .',
                        ];
                    }

                    $prevColumn = $column;
                } else {
                    throw new \Exception("Invalid column: name is required " . json_encode($column));
                }
            }
        }

		if($saveData){
			$this->saveData($tableName, $targetDir);
		}
        return $tableName;
    }

    /**
     * instad of trying to execute query to alter tale
     * this will query the column into the table first to
     * check if it is exist or not
     * @param $tableName string
     * @param $columnName string
     */
    public function fetchColumnName($tableName, $columnName){
        $connection = $this->_connection->getConnection()->getConnection();
        $sql = "SELECT `".$columnName."` FROM `".$tableName."` limit 1";

        $isExist = $this->executeQuery($connection, $sql);
        return $isExist;
    }

    /**
     * try to query the table name to check if it is exist or not
     * return boolean
     * @param $tableName string the table name to be checked
     */
    public function fetchTableName($tableName){
        $connection = $this->_connection->getConnection()->getConnection();
        $sql = "SELECT * FROM `".$tableName."` limit 1";
        
        $isExist = $this->executeQuery($connection, $sql);
        return $isExist;
    }

    private function executeQuery($connection, $sql, $unsecureValue=[]){
        $isExist = false;
        try {
            $sth = $connection->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $sth->execute($unsecureValue);
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
            $isExist = true;
        } catch (\Exception $e) {
            $isExist = false;
        }
        return $isExist;
    }

    /**
     * try to save preinstalled data id the JSON data exist
     * if he data exist check if it is already saved before or not
     * @param $tableName string
     * @param $targetDir string
     */
    private function saveData($tableName, $targetDir){
        $prefix = $this->_connection->getConfig('table_prefix');
        $tableName = str_replace($prefix, '', $tableName);
        $dataFilename = $tableName . '_data.json';

        $dataJSONFile = $targetDir.DS.$dataFilename;

        if(file_exists($dataJSONFile)){
            $_data = file_get_contents($dataJSONFile);
            $_data = json_decode($_data, true);

            foreach ($_data as $key => $data) {
                try {
					if(isset($data['_migration_data_save_'])){
						/**
						 * TODO: change how this use the entity to save data
						 * without changing JSON file schema
						 */
						$saveSrc = null;
						foreach ($data as $_key => $_value) {
							if(!is_string($value)){
								if($_key == '_migration_data_save_'){
									$saveSrc = $value;
								}
							}
						}
						if($saveSrc){
							$src = $this->_di->get($saveSrc['source']);
							$method = $value['method'];
							$src->$method($data);
						}
					}
					else {
						$this->saveInstallationdata($data, $tableName);
						// $data = $this->buildDataForSaving($data);
						// $connection = $this->_connection->getConnection();
						// $connection->insert($tableName, $data);
					}
                } catch (\Exception $e) {
                    /** do error message here */
                }
            }
        }
    }

	/**
	 * build the data came from JSON file
	 */
	public function buildDataForSaving($data){
		$d = [];
		foreach ($data as $fieldName => $value) {
			if(isset($value['option'])){
				$opt = $value['option'];
				if(isset($opt['is_hashed']) && $opt['is_hashed'] == true){
					$value['value'] = $this->_password->setPassword($value['value'])->getHash();
				}
			}
			$d[$fieldName] = $value['value'];
		}
		return $d;
	}

    /**
     * return the files inside dir
     */
    protected function getDirFiles($dir){
        if(is_dir($dir)){
            $files = scandir($dir);
            if(count($files) >= 3){
                unset($files[0]);
                unset($files[1]);
            }
            sort($files);

            return $files;
        }
    }

	/**
     * @param $tableName string
     * @param $constraintName string
	 */
	public function checkConstraintIfAdded($tableName, $constraintName){
		$isExist = false;

		$checkConstraintQuery = "";
		try {
			// $checkConstraintQuery = "SHOW INDEXES IN `".$tableName."` WHERE `Key_name` = '".$constraintName."'";

			$checkConstraintQuery = "
			SELECT * FROM information_schema.key_column_usage
			WHERE `CONSTRAINT_NAME` = '".$constraintName."'
			AND `TABLE_SCHEMA` = '".$this->_connection->getConfig('database')."' 
			AND `TABLE_NAME` = '".$tableName."'";

			$checkConstraint = $this->_connection->getConnection()->fetchAll($checkConstraintQuery);

			if(count($checkConstraint)){
				$isExist = true;
			}
		} catch (\PDOException $pe) {
			throw new \Exception($pe->getMessage());
		}
		return $isExist;
	}

    /**
     * add foreignkey to the table
     * @param $tableName string
     * @param $column string
     * @param $referenceTableName string
     * @param $referenceColumn string
     * @param $onDelete string
     * @param $onUpdate string
     * @param $constraintName string
	 * 
	 * reference https://www.mysqltutorial.org/mysql-foreign-key/
     */
	public function addForeignKey(
		$tableName, 
		$column, 
		$referenceTableName, 
		$referenceColumn, 
		$onDelete='ON DELETE CASCADE', 
		$onUpdate='', 
		$constraintName=null
	){	
        $tableName = $this->_connection->getTablename($tableName);
        $referenceTableName = $this->_connection->getTablename($referenceTableName);

		$isExist = false;
		$query = "ALTER TABLE `".$tableName."` ";
		if($constraintName){
			$isExist = $this->checkConstraintIfAdded($tableName, $constraintName); /** we nned to check if the name exist or not */
			$query .= " ADD CONSTRAINT `".$constraintName."` FOREIGN KEY (`".$column."`) ";
		}
		else {
			$query .= " ADD FOREIGN KEY (`".$column."`) ";
		}
		$query .= " REFERENCES ".$referenceTableName."(`".$referenceColumn."`) ";

		$query .= ' ' . $onDelete . ' ' . $onUpdate . ';';
		
		$connection = $this->_connection->getConnection()->getConnection();
		if($isExist){ /** drop the constraint first to update */
			try {
				$dropQuery = "ALTER TABLE ".$tableName." DROP FOREIGN KEY ".$constraintName.";";
				$result =  $connection->exec($dropQuery);
			} catch (\PDOException $pe) {
				throw new \Exception($pe->getMessage());
			}
		}

		try {
			$result =  $connection->exec($query);
		} catch (\PDOException $pe) {
			var_dump($pe->getMessage());
			die;
			throw new \Exception($pe->getMessage());
		}
	}

	/**
	 * check if the table name that was scanned in DIR
	 * is for for data or for table name
	 */
	public function checkIfFileIsData($tableName){
		$tableName = explode('.', $tableName);
		if(count($tableName) >= 2){
			unset($tableName[count($tableName) - 1]);
		}
		$tableName = explode('_', $tableName[0]);
		if(count($tableName) >= 2){
			if($tableName[count($tableName) - 1] == 'data'){
				return true;
			}
		}
		return false;
	}

	/**
	 * return the rimary key of the data came from JSON file 
	 * installtion data
	 * @param $data came from JSON installtion data file
	 * return null || array
	 */
	public function getPrimaryData($data){
		$primaryKey = null;
		foreach ($data as $key => $value) {
			if(isset($value['value']) && !empty($value['value']) && isset($value['option'])){
				if(isset($value['option']['primary']) && $value['option']['primary'] == true){
					$primaryKey = [
						'name' => $key,
						'value' => $value['value']
					];
				}
			}
		}

		return $primaryKey;
	}

	/**
	 * check if the value of installation data was already in database or not
	 * @param $primaryKey must be the return of the getPrimaryData($data) method
	 * @param $tableName string
	 * return array
	 */
	public function checkIfJsonInstallationDataPrimaryKeyExist($primaryKey, $tableName){
		$isExist = [];
		if(is_array($primaryKey)){
			$tableName = $this->_connection->getTablename($tableName);
			$di = new \Of\Std\Di();
			$select = $di->get('\Of\Database\Sql\Select');
			$select->select()->from($tableName);
			$select->where($primaryKey['name'])->eq($primaryKey['value']);

			$connection = $this->_connection->getConnection();
			$isExist = $connection->fetchAll($select->getQuery(), $select->_whereStatement->unsecureValue);
		}
		return $isExist;
	}

	/**
	 * this will check if the installation data was already exisitng in the 
	 * database or not
	 * this will simply query all column with value from JSON file
	 */
	public function saveInstallationdata($data, $tableName){
		$_data = $this->buildDataForSaving($data);
		$tn = $this->_connection->getTablename($tableName);

		$primaryKey = $this->getPrimaryData($data);

		$isExist = $this->checkIfJsonInstallationDataPrimaryKeyExist($primaryKey, $tableName);
		$connection = $this->_connection->getConnection();

		try {
			if(count($isExist) > 0){
				/** data exist */
				$di = new \Of\Std\Di();
				$select = $di->get('\Of\Database\Sql\Select');
				$connection->update($select, [$primaryKey['name'] => $primaryKey['value']], $_data, $tn);
			}
			else {
				/** not found */
				$connection->insert($tn, $_data);
			}
		} catch (\PDOException $pe) {
			throw new \Exception($pe->getMessage(), 500);
		}
	}

	public function saveConstraintsInJSON($vendor, $module, $tableName, $constraints, $isSave=false){
		$targetFile = Constants::EXT_DIR.DS.$vendor.DS.$module.Constants::MODULE_DB_SCHEMA_DIR.DS.'relationship.json';
		
		$result = [
			'errors_message' => [],
			'message' => [],
		];

		$jsonData = [];
		if(file_exists($targetFile) && is_file($targetFile)){
			$jsonData = file_get_contents($targetFile);
			$jsonData = json_decode($jsonData, true);

			if(json_last_error() == JSON_ERROR_NONE){
				foreach ($jsonData as $key => $value) {
					if($value['tablename'] == $tableName){
						unset($jsonData[$key]);
					}
				}
			}
		}

		foreach ($constraints as $key => $constraint) {
			$jsonData[] = $constraint;
			if($isSave){
				$this->addForeignKey(
					$constraint['tablename'], 
					$constraint['column'], 
					$constraint['reference_tablename'], 
					$constraint['reference_column'], 
					$constraint['on_delete'], 
					$constraint['on_updated'], 
					$constraint['constraint_name']
				);

				try {
					$result['message'][] = $constraint['constraint_name'] . ' successfully installed into the database.';
				} catch (\Exception $e) {
					$result['errors_message'][] = $constraint['constraint_name'] . ' failed: ' . $e->getMessage();
				}
			}
		}

		sort($jsonData);

		$_jsonData = json_encode($jsonData, JSON_PRETTY_PRINT);

		$this->_writer->setDirPath(dirname($targetFile))
		->setData($_jsonData)
		->setFilename('relationship')
		->setFileextension('json')
		->write();

		$result['message'][] = 'Constraints successfully saved in ' . $vendor.'_'.$module.Constants::MODULE_DB_SCHEMA_DIR.DS.'relationship.json';

		return $result;
	}
}
?>