<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseSaveConstraint extends Sys {

	
	protected $pageTitle = 'Opoink Database';

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

			$constraints = $this->_request->getParam('constraints');
			$_module = $this->_request->getParam('module');
			$tablename = $this->_request->getParam('tablename');
			$saveAndInstall = $this->_request->getParam('save_and_install');

			if(!$tablename){
				$this->returnError('406', 'The tablename is required');
			}
			if(count($constraints) <= 0){
				$this->returnError('406', 'The constraints is required');
			}

			if($_module){
				$_module = explode('_', $_module);
				if(count($_module) == 2){
					list($vendor, $module) = $_module;

					try {
						$data = $this->_moduleAvailableTables->setConfig($this->_config)->saveConstraintsInJSON($vendor, $module, $tablename, $constraints, $saveAndInstall);
						$this->jsonEncode($data);
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