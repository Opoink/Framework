<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

class Css {
	
	protected $_config;
	protected $_lessBuilder;
	protected $_scssBuilder;
	
	public function __construct(
		\Of\Config $Config,
		\Of\Less\Builder $LessBuilder,
		\Of\File\ScssBuilder $ScssBuilder
	){
		
		$this->_config = $Config;
		$this->_lessBuilder = $LessBuilder;
		$this->_scssBuilder = $ScssBuilder;
	}
	
	public function run($file){

		$css = null;

		$fArray = explode('.', $file);
		if(in_array('scss', $fArray)){
			$css = $this->_scssBuilder->setConfig($this->_config)->build($file);
		}
		else {
			$css = $this->_lessBuilder
			->setConfig($this->_config)
			->build($file);
		}


		if($css){
			echo header("Content-type: text/css", true);
			echo $css;
			exit;
			die;
		} else {
			return false;
		}
	}
}

?>