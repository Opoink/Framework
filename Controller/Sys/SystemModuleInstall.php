<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemModuleInstall extends Sys {
	
	protected $pageTitle = 'Opoink Module List';
	protected $_modManager;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\ModManager\Module $modManager
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_modManager = $modManager;
	}
	
	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		$validate = $this->validateFormKey();
		if($validate){
			$availableModules = $this->_request->getParam('availableModule');
			$installed = $this->_modManager->setDi($this->_di)->installModule($availableModules);
			
			$installedCount = count($installed);
			$s = ($installedCount > 1) ? 's' : '';

			$response['error'] = 0;
			$response['message'] = $installedCount.' Module' . $s . ' successfully installed';
			$this->jsonEncode($response);
		} else {
			$this->returnError('400', 'Invalid formkey request');
		}
	}
}