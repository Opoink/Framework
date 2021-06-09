<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemStaticVue extends Sys {

	protected $pageTitle = '';
	protected $_sysVueRenderer;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\View\Vue\SysVueRenderer $SysVueRenderer
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_sysVueRenderer = $SysVueRenderer;
	}

	public function run(){
		$this->_sysVueRenderer->getComponents();

		echo header("Content-type: application/javascript", true);
		echo $this->_sysVueRenderer->toJs();
		exit;
		die;
	}
}