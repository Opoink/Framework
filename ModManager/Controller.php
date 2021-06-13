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
	protected $extends = '\\Of\\Controller\\Controller';

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

	/*
	 * set the class where to extend
	 * the controller that will be created
	 */
	public function setExtends($extends){
		$this->extends = $extends;
		return $this;
	}

	/**
	 * create the controller file the Route/Controller/Action(RCA) type
	 */
	public function create($type='public'){
		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor.DS.$this->module.DS.'Controller';

		$route = $this->route;
		$controller = $this->controller;
		$action = $this->action;

		$data = "<?php" . PHP_EOL;

		if($type == 'admin'){
			$target .= DS.'Admin';
			$data .= "namespace ".$this->vendor."\\".$this->module."\\Controller\\Admin\\".$route."\\".$controller.";" . PHP_EOL . PHP_EOL;
		} else {
			$data .= "namespace ".$this->vendor."\\".$this->module."\\Controller\\".$route."\\".$controller.";" . PHP_EOL . PHP_EOL;
		}
		$target .= DS.$route.DS.$controller;

		/*$data .= "class ".$action." extends \\Of\\Controller\\Controller {" . PHP_EOL . PHP_EOL;*/
		$data .= "class ".$action." extends ".$this->extends." {" . PHP_EOL . PHP_EOL;
			$data .= "\tprotected \$pageTitle = '".$route." ".$controller." ".$action."';" . PHP_EOL;
			$data .= "\tprotected \$_url;" . PHP_EOL;
			$data .= "\tprotected \$_message;" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function __construct(" . PHP_EOL;
				$data .= "\t\t\Of\Http\Url \$Url," . PHP_EOL;
				$data .= "\t\t\Of\Std\Message \$Message" . PHP_EOL;
			$data .= "\t){" . PHP_EOL . PHP_EOL;
				$data .= "\t\t\$this->_url = \$Url;" . PHP_EOL;
				$data .= "\t\t\$this->_message = \$Message;" . PHP_EOL;
			$data .= "\t}" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function run(){" . PHP_EOL;
				$data .= "\t\treturn parent::run();" . PHP_EOL;
			$data .= "\t}" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function setLayout(\$router){" . PHP_EOL;
				$data .= "\t\t\$this->_url->setRouter(\$router);" . PHP_EOL;
				$data .= "\t\treturn parent::setLayout(\$router);" . PHP_EOL;
			$data .= "\t}" . PHP_EOL;

		$data .= "}" . PHP_EOL;
		$data .= "?>" . PHP_EOL;

		$file = $target . DS . $action.'.php';

		if(file_exists($file) && is_file($file)){
			return false;
		} else {
			$_writer = new \Of\File\Writer();
			$_writer->setDirPath($target)
			->setData($data)
			->setFilename($action)
			->setFileextension('php')
			->write();

			return true;
		}
	}

	/**
	 * create a pattern type controller
	 */
	public function createPattern($patternRegex, $patternForFileCreate, $target){	
		$data = "<?php" . PHP_EOL;

		$nSpace = $patternForFileCreate;
		array_pop($nSpace);
		$data .= "namespace ".$this->vendor."\\".$this->module."\\Controller\\".implode('\\', $nSpace).";" . PHP_EOL . PHP_EOL;

		$className = end($patternForFileCreate); 
		$data .= "class ".$className." extends ".$this->extends." {" . PHP_EOL . PHP_EOL;
			$data .= "\tprotected \$pageTitle = '';" . PHP_EOL;
			$data .= "\tprotected \$_url;" . PHP_EOL;
			$data .= "\tprotected \$_message;" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function __construct(" . PHP_EOL;
				$data .= "\t\t\Of\Http\Url \$Url," . PHP_EOL;
				$data .= "\t\t\Of\Std\Message \$Message" . PHP_EOL;
			$data .= "\t){" . PHP_EOL . PHP_EOL;
				$data .= "\t\t\$this->_url = \$Url;" . PHP_EOL;
				$data .= "\t\t\$this->_message = \$Message;" . PHP_EOL;
			$data .= "\t}" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function run(){" . PHP_EOL;
				
				$data .= "\t\t\$this->toJson(\$this->getParam());" . PHP_EOL;
				$data .= "\t\t/** this is used when using the XML template */" . PHP_EOL;
				$data .= "\t\t/**return parent::run();*/" . PHP_EOL;
			$data .= "\t}" . PHP_EOL . PHP_EOL;

			$data .= "\tpublic function setLayout(\$router){" . PHP_EOL;
				$data .= "\t\t\$this->_url->setRouter(\$router);" . PHP_EOL;
				$data .= "\t\treturn parent::setLayout(\$router);" . PHP_EOL;
			$data .= "\t}" . PHP_EOL;

		$data .= "}" . PHP_EOL;
		$data .= "?>" . PHP_EOL;

		$dirPath = dirname($target);

		return $this->writeClass($target, $dirPath, $data, $className);
	}

	private function writeClass($file, $dirPath, $data, $fileName){
		if(file_exists($file) && is_file($file)){
			return false;
		} else {
			$_writer = new \Of\File\Writer();
			$_writer->setDirPath($dirPath)
			->setData($data)
			->setFilename($fileName)
			->setFileextension('php')
			->write();

			return true;
		}
	}
}