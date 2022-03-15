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
		if($alltables == 1){

			$allTables = $this->_moduleAvailableTables->setConfig($this->_config)->getAllInstalledAvailableStable();
			$this->jsonEncode($allTables);
		}
		else {
			return $this->renderHtml();
		}
	}
}