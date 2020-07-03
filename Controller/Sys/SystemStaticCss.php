<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemStaticCss extends Sys {

	protected $pageTitle = '';
	protected $_sysVueRenderer;

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
		echo header("Content-type: text/css", true);
		$target = dirname(dirname(dirname(__FILE__))) . DS . 'View' . DS . 'Sys' . DS . 'Css' . DS . 'style.css';

		$this->loop(0, 200, 5, 'm-', '', 'margin', 'px');
		$this->loop(0, 200, 5, 'mT-', '', 'margin-top', 'px');
		$this->loop(0, 200, 5, 'mR-', '', 'margin-right', 'px');
		$this->loop(0, 200, 5, 'mL-', '', 'margin-left', 'px');
		$this->loop(0, 200, 5, 'mB-', '', 'margin-bottom', 'px');

		$this->loop(0, 200, 5, 'p-', '', 'padding', 'px');
		$this->loop(0, 200, 5, 'pT-', '', 'padding-top', 'px');
		$this->loop(0, 200, 5, 'pR-', '', 'padding-right', 'px');
		$this->loop(0, 200, 5, 'pL-', '', 'padding-left', 'px');
		$this->loop(0, 200, 5, 'pB-', '', 'padding-bottom', 'px');

		$this->loop(0, 30, 1, 'fs-', '', 'font-size', 'px');
		$this->loop(0, 900, 100, 'fw-', '', 'font-weight', '');

		if(file_exists($target)){
			include($target);
		}
		exit;
		die;
	}

	protected function loop($from, $to, $interval, $prefix, $suffix, $property, $unit){
		$str = '';
		for ($i = $from; $i < $to; $i) { 

			$str .= "." . $prefix . $i . $suffix . " {";
				$str .= $property . ": " . $i . $unit . ";";
			$str .= "}" . PHP_EOL;
			
			$i = $i + $interval;
		}

		echo $str;
	}
}