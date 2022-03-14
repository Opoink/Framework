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
		$referrer = $this->_request->getServer('HTTP_REFERER');

		/**
		 * this javascript should run only if called in system pages
		 */
		if($referrer){
			$referrer = parse_url($referrer);
			if(isset($referrer['host']) && $referrer['host'] == $this->_url->getDomain()){
				$this->_sysVueRenderer->getComponents();
				$js = $this->_sysVueRenderer->toJs();
				echo header("Content-type: application/javascript", true);
				echo $js;
				exit;
				die;
			}
			else {
				$this->returnError('404', 'Page Not Found.');
			}
		}
		else {
			$this->returnError('404', 'Page Not Found.');
		}
	}
}