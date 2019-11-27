<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\ModManager;

class Controller {

	protected $route;
	protected $controller;
	protected $action;
	protected $vendor;
	protected $module;

	/**
	 * set the route to be created
	 */
	public function setVendor($vendor){
		$this->vendor = ucfirst($vendor);
		return $this;
	}

	/**
	 * set the route to be created
	 */
	public function setModule($module){
		$this->module = ucfirst($module);
		return $this;
	}

	/**
	 * set the route to be created
	 */
	public function setRoute($route){
		$this->route = ucfirst($route);
		return $this;
	}

	/**
	 * set the controller to be created
	 */
	public function setController($controller){
		$this->controller = ucfirst($controller);
		return $this;
	}

	/**
	 * set the action to be created
	 */
	public function setAction($action){
		$this->action = ucfirst($action);
		return $this;
	}

	public function create($type='public'){
		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor.DS.$this->module.DS.'Controller';

		$data = "<?php" . PHP_EOL;

		if($type == 'admin'){
			$target .= DS.'Admin';
			$data .= "namespace ".$this->vendor."\\".$this->module."\\Controller\\Admin\\".$this->route."\\".$this->controller.";" . PHP_EOL . PHP_EOL;
		} else {
			$data .= "namespace ".$this->vendor."\\".$this->module."\\Controller\\".$this->route."\\".$this->controller.";" . PHP_EOL . PHP_EOL;
		}
		$target .= DS.$this->route.DS.$this->controller;

		$data .= "class ".$this->action." extends \\Of\\Controller\\Controller {" . PHP_EOL . PHP_EOL;
			$data .= "\tprotected \$pageTitle = '".$this->route." ".$this->controller." ".$this->action."';" . PHP_EOL . PHP_EOL;
			$data .= "\tpublic function run(){" . PHP_EOL;
				$data .= "\t\treturn parent::run();" . PHP_EOL;
			$data .= "\t}" . PHP_EOL;
		$data .= "}" . PHP_EOL;
		$data .= "?>" . PHP_EOL;

		$file = $target . DS . $this->action.'.php';

		if(file_exists($file) && is_file($file)){
			return false;
		} else {
			$_writer = new \Of\File\Writer();
			$_writer->setDirPath($target)
			->setData($data)
			->setFilename($this->action)
			->setFileextension('php')
			->write();

			return true;
		}
	}
}