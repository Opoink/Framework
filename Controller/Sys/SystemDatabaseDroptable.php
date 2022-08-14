<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemDatabaseDroptable extends Sys {

	
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
			$tablename = $this->_request->getParam('table/tablename');
			$_module = $this->_request->getParam('module');
			$action = $this->_request->getParam('table/action');

			if(!$tablename){
				$this->returnError('406', 'The table name is required');
			}

			if($_module){
				$_module = explode('_', $_module);
				if(count($_module) == 2){
					list($vendor, $module) = $_module;

					try {
						$result = $this->_moduleAvailableTables->dropTable($vendor, $module, $tablename, $action);
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