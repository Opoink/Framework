<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleCreate extends Sys {

	
	protected $pageTitle = 'Module Creator';

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		return $this->renderHtml();
	}
}