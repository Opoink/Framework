<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleSave extends Sys {

	
	protected $pageTitle = 'Opoink Module Save';
	protected $_versionvalidator;
	protected $_configManager;
	protected $_validator;
	protected $_schema;
	protected $_xml;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Std\Versionvalidator $Versionvalidator,
		\Of\ModManager\Config $_Config,
		\Of\ModManager\Validator $Validator,
		\Of\ModManager\Schema $Schema,
		\Of\ModManager\Xml $Xml
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_versionvalidator = $Versionvalidator;
		$this->_configManager = $_Config;
		$this->_validator = $Validator;
		$this->_schema = $Schema;
		$this->_xml = $Xml;
	}

	

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		$module_ver = $this->_request->getParam('module_ver');

		$isValidVer = $this->_versionvalidator->validate($module_ver);

		$redirectUrl = '/system/module/create';

		if($isValidVer){
			$vendor_name = ucfirst($this->_request->getParam('vendor_name'));
			$module_name = ucfirst($this->_request->getParam('module_name'));

			if(!$this->_validator->checkExist($vendor_name, $module_name)){
				$this->_configManager->setVendor($vendor_name)
				->setModule($module_name)
				->setVersion($module_ver)
				->createConfig();

				/** check the config if it is created */
				if($this->_validator->checkExist($vendor_name, $module_name)){
					$this->_schema->create($vendor_name, $module_name);

					/* this part is the creation of other directory */
					$target = ROOT.DS.'App'.DS.'Ext'.DS.$vendor_name.DS.$module_name;

					$dirMan = new \Of\File\Dirmanager();

					$dirMan->createDir($target.DS.'Entity');
					$dirMan->createDir($target.DS.'View'.DS.'Layout'.DS.'Admin');
					$dirMan->createDir($target.DS.'View'.DS.'Template');

					$this->_xml->setVendor($vendor_name)
					->setModule($module_name)
					->setFileName('default')
					->create();

					$response['error'] = 0;
					$response['message'] = 'New module successfully created.';
					$this->jsonEncode($response);
				} else {
					$this->returnError('400', 'New module successfully created.');
				}
			} else {
				$this->returnError('400', 'Module name already exist.');
			}
		} else {
			$this->returnError('400', 'Invalid version format.');
		}
	}
}