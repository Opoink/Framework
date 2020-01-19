<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleEdit extends Sys {

	
	protected $pageTitle = 'Module Editor';

	/**
	 *	holds the existing module config array
	 */
	protected $config;

	protected $_validator;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\ModManager\Validator $Validator
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_validator = $Validator;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		$this->addInlineJs();

		$mod = $this->getParam('mod');  
		$mod = explode('_', $mod);

		if(count($mod) == 2){
		    $vendorName = ucfirst($mod[0]);
		    $moduleName = ucfirst($mod[1]);

		    $this->config = $this->_validator->checkExist($vendorName, $moduleName);

		    if(!$this->config){
		    	$this->_message->setMessage('Module '.$vendorName.'_'.$moduleName.' is not exist.', 'danger');
		    }
		}



		return $this->renderHtml();
	}
}