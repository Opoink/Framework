<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseDropfield extends Sys {

	
	protected $pageTitle = 'Opoink Database';
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
			$tablename = $this->_request->getParam('tablename');
			$_module = $this->_request->getParam('module');
			$save_and_install = $this->_request->getParam('save_and_install');

			if(!$tablename){
				$this->returnError('406', 'The table name is required');
			}

			if($_module){
				$_module = explode('_', $_module);
				if(count($_module) == 2){
					list($vendor, $module) = $_module;

					$fields = $this->_request->getParam('fields');
					if(is_array($fields)){
						$allResult = [
							"database_drop" => [],
							"json_remove" => []
						];

						$this->_moduleAvailableTables->setConfig($this->_config);
						try {	
							$drop_check = $this->_request->getParam('drop_check');
							if($drop_check){
								$allResult["database_drop"] = $this->_moduleAvailableTables->dropFieldsFromDatabase($tablename, $fields);
							}
						} catch (\Exception $e) {
							$this->returnError('500', $e->getMessage());
						}

						try {	
							$remove_on_json = $this->_request->getParam('remove_on_json');
							if($remove_on_json){
								$allResult["json_remove"] = $this->_moduleAvailableTables->removeFieldsFromJsonFile($vendor, $module, $tablename, $fields);
							}
						} catch (\Exception $e) {
							$this->returnError('500', $e->getMessage());
						}

						$this->jsonEncode($allResult);
					}
					else {
						$this->returnError('406', 'Fields should be an array');
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