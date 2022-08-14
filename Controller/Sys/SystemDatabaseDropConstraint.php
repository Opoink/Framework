<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseDropConstraint extends Sys {

	
	protected $pageTitle = 'Opoink Database Drop Constraint';
	protected $_moduleAvailableTables;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Database\Migration\ModuleAvailableTables $ModuleAvailableTables
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_moduleAvailableTables = $ModuleAvailableTables;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		if($this->validateFormKey()){
			$_module = $this->_request->getParam('module');

			if($_module){
				$_module = explode('_', $_module);
				if(count($_module) == 2){
					list($vendor, $module) = $_module;

					$this->_moduleAvailableTables->setConfig($this->_config);
					try {
						$removeInJsonFile = $this->_request->getParam('remove_in_json_file');
						$dropInDatabase = $this->_request->getParam('drop_in_database');
						$tableName = $this->_request->getParam('column/tablename');
						$constraintName = $this->_request->getParam('column/constraint_name');

						if(!$tableName){
							$this->returnError('406', 'The tablename is required');
						}
						if(!$constraintName){
							$this->returnError('406', 'The constraint name is required');
						}

						$result = [
							'errors_message' => [],
							'message' => [],
						];

						if($removeInJsonFile){
							$result['message'][] = $this->_moduleAvailableTables->removeConstraintInJsonFile($vendor, $module, $tableName, $constraintName);
						}

						if($dropInDatabase){
							try {
								$this->_moduleAvailableTables->dropConstraint($tableName, $constraintName);
								$result['message'][] = 'Constraint '.$constraintName.' successfully dropped';
							} catch (\Exception $e) {
								$result['errors_message'][] = [
									'code' => $e->getCode(),
									'message' => $e->getMessage()
								];
							}
						}

						$this->jsonEncode($result);
					} catch (\Exception $e) {
						$this->returnError($e->getCode(), $e->getMessage());
					}
				}
				else {
					$this->returnError('406', 'Invalid vendor, module format. Should be vendorname_modulename');
				}
			}
			else {
				$this->returnError('406', 'The module name is required');
			}
		}
		else {
			header("HTTP/1.0 400 Bad Request");
			echo 'Invalid request';
			die;
		}
	}
}