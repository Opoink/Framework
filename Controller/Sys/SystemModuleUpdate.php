<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleUpdate extends Sys {

	
	protected $pageTitle = 'Opoink Module Update';
	protected $_versionvalidator;
	protected $_configManager;
	protected $_validator;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Std\Versionvalidator $Versionvalidator,
		\Of\ModManager\Config $_Config,
		\Of\ModManager\Validator $Validator
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_versionvalidator = $Versionvalidator;
		$this->_configManager = $_Config;
		$this->_validator = $Validator;
	}

	

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		$module_ver = $this->_request->getParam('module_ver');

		$isValidVer = $this->_versionvalidator->validate($module_ver);

		$vendor_name = ucfirst($this->_request->getParam('vendor_name'));
		$module_name = ucfirst($this->_request->getParam('module_name'));

		$redirectUrl = '/system/module/edit/mod/'.$vendor_name.'_'.$module_name;

		if($isValidVer){

			$config = $this->_validator->checkExist($vendor_name, $module_name);
			if($config){
				$config['version'] = $module_ver;

				$this->_configManager->setConfig($config)->createConfig();

				$response['error'] = 0;
				$response['message'] = $vendor_name . '_' . $module_name . ' module successfully updated';
				$this->jsonEncode($response);
			} else {
				$this->returnError('400', 'Module name does not exist.');
			}
		} else {
			$this->returnError('400', 'Invalid version format.');
		}
	}
}