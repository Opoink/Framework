<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseIndex extends Sys {

	
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

		$alltables = (int)$this->_request->getParam('alltables');
		$_module = $this->_request->getParam('module');
		$tablename = $this->_request->getParam('tablename');

		if($alltables == 1){
			$allTables = $this->_moduleAvailableTables->setConfig($this->_config)->getAllInstalledAvailableStable();
			$this->jsonEncode($allTables);
		}
		elseif($_module && $tablename){
			$_module = explode('_', $_module);
			if(count($_module) == 2){
				list($vendor, $module) = $_module;

				$fields = $this->_moduleAvailableTables->setConfig($this->_config)->getFieldsByVendorModuleAndTableName($vendor, $module, $tablename);
				if($fields){
					$this->jsonEncode($fields);
				}
				else {
					$this->returnError('406', 'Invalid JSON format, or the file not exist.');
				}
			}
			else {
				$this->returnError('406', 'Invalid module');
			}
		}
		else {
			return $this->renderHtml();
		}
	}
}