<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
* 
*/
namespace Of\ModManager;

class Config {

	protected $config = [
		'vendor' => '',
		'module' => '',
		'version' => '',
		'controllers' => [],
	];

	/**
	 * set the vendor name
	 */
	public function setVendor($vendor){
		$this->config['vendor'] = $vendor;
		return $this;
	}

	/**
	 * set the module name
	 */
	public function setModule($module){
		$this->config['module'] = $module;
		return $this;
	}

	/**
	 * set the version
	 */
	public function setVersion($version){
		$this->config['version'] = $version;
		return $this;
	}

	/**
	 * add controller
	 * @param $key string, route to be used on opoink routing system
	 * @param $value string, the controller class to be used
	 */
	public function addController($key, $value){
		$this->config['controllers'][] = $value;
		return $this;
	}

	/**
	 * set the config if it is exist so that
	 * during the create the existing will not going to be overridden
	 * @param $config array, the existing config of the module
	 * this is for update of the config
	 */
	public function setConfig($config){
		$this->config = $config;
		return $this;
	}

	/**
	 * create config file
	 * this will not going to upate the existing 
	 * but override it.
	 */
	public function createConfig(){
		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->config['vendor'].DS.$this->config['module'];

		$data = '<?php' . PHP_EOL;
		$data .= 'return ' . var_export($this->config, true) . PHP_EOL;
		$data .= '?>';

		$_writer = new \Of\File\Writer();
		$_writer->setDirPath($target)
		->setData($data)
		->setFilename('Config')
		->setFileextension('php')
		->write();
	}
}