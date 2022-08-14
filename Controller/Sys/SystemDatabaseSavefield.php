<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseSavefield extends Sys {

	
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

					$this->_moduleAvailableTables->setConfig($this->_config);

					$install_table = $this->_request->getParam('install_table');
					if($install_table){ /** the request is to install the table */
						$this->_moduleAvailableTables->_createTable($vendor, $module, $tablename);
					}
					else {
						$fields = $this->_request->getParam('fields');
						if(is_array($fields)){
							try {	
								$this->_moduleAvailableTables->saveFieldsByVendorModuleTablename($vendor, $module, $tablename, $fields, $save_and_install);
							} catch (\Exception $e) {
								$this->returnError('406', $e->getMessage());
							}
						}
						else {
							$this->returnError('406', 'Fields should be an array');
						}
					}

					$fields = $this->_moduleAvailableTables->getFieldsByVendorModuleAndTableName($vendor, $module, $tablename);
					if($fields){
						$this->jsonEncode($fields);
					}
					else {
						$this->returnError('406', 'Invalid JSON format, or the file not exist.');
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