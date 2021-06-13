<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleAddcontroller extends Sys {

	const RCA = 'RCA';
	const PATTERN = 'PATTERN';
	
	protected $pageTitle = 'Opoink Module Update';
	protected $_configManager;
	protected $_controller;
	protected $_validator;
	protected $_xml;

	protected $config;
	protected $module_ver;
	protected $vendor_name;
	protected $module_name;
	protected $redirectUrl;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\ModManager\Config $_Config,
		\Of\ModManager\Controller $Controller,
		\Of\ModManager\Validator $Validator,
		\Of\ModManager\Xml $Xml
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_configManager = $_Config;
		$this->_controller = $Controller;
		$this->_validator = $Validator;
		$this->_xml = $Xml;
	}

	

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		$this->module_ver = $this->_request->getParam('module_ver');

		$this->vendor_name = ucfirst($this->_request->getParam('vendor_name'));
		$this->module_name = ucfirst($this->_request->getParam('module_name'));

		$this->redirectUrl = '/system/module/edit/mod/'.$this->vendor_name.'_'.$this->module_name;

		$this->config = $this->_validator->checkExist($this->vendor_name, $this->module_name);
		if($this->config){
			$type = $this->_request->getParam('type');

			if($type == self::RCA){
				$this->createRCAController();
			} elseif ($type == self::PATTERN) {
				$this->createPatternController();
			}


		} else {
			$this->returnError('400', 'Module name does not exist.');
		}
	}

	private function createPatternController(){
		$controller_pattern = $this->_request->getParam('controller_pattern');
		$controller_request_method = $this->_request->getParam('controller_request_method');
		$extends = $this->_request->getParam('extend_to_class');

		$pattern = rtrim(ltrim($controller_pattern, '/'), '/');
		$pattern = explode('/', $pattern);

		if($pattern[0] == 'system' || $pattern[0] == 'System'){
			$this->returnError('400', 'System route is reserved for Opoink\'s developer panel.');
		} else {
			$patternForFileCreateArray = [];
			$patternRegexArray = [];
			foreach ($pattern as $key => $value) {

				/** clean for pattern but let alphnumeric char, : and {} */
				$value = preg_replace("/[^A-Za-z0-9:{}\-\_]/", "", $value);
				$patternRegexArray[$key] = strtolower($value);

				/** clear for php namespace and file creation */
				$value = preg_replace("/[^A-Za-z0-9\-\_]/", "", $value);
				$value = ucwords(preg_replace("/[^A-Za-z0-9]/", " ", $value));
				$value = preg_replace("/[^A-Za-z0-9]/", "", $value);
				
				$patternForFileCreateArray[$key] = ucfirst($value);
			}

			$patternRegex = '/'.implode('/', $patternRegexArray);
			$patternForFileCreate = implode(DS, $patternForFileCreateArray);
			$patternForNameSpace = implode('\\', $patternForFileCreateArray);
			
			$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor_name.DS.$this->module_name.DS.'Controller'.DS.$patternForFileCreate.'.php';
			if(!file_exists($target)){
				if(isset($this->config['controllers'])){
					foreach ($this->config['controllers'] as $key => $controller) {
						if(is_array($controller)){
							if(isset($controller['pattern'])){
								if($controller['pattern'] == $patternRegex){
									$this->returnError('406', 'Route already exist.');
								} 
							}
						}
					}

					/**
					 * this is to try if the class to extend exists
					 * if it is not injector will raise an error
					 */
					if(!empty($extends)){
						try {
							$this->_di->make($extends);
						} catch (\Exception $e) {
							$this->returnError('406', $e->getMessage());
						}
						$this->_controller->setExtends($extends);
					}

					$create = $this->_controller->setVendor($this->vendor_name)
					->setModule($this->module_name)
					->createPattern($patternRegexArray, $patternForFileCreateArray, $target);
					if($create){
						$routerInfo = [
							'pattern' => $patternRegex,
							'class' => $this->vendor_name.'\\'.$this->module_name.'\\Controller\\'.$patternForNameSpace,
							'page_name' => $patternForNameSpace,
							'method' => $controller_request_method,
						];

						/** insert into module config */
						$this->config['controllers'][] = $routerInfo;
		
						$this->_configManager->setConfig($this->config)
						->createConfig();
						/** end insert into module config */

						/** insert into installation config */
						$_config = ROOT . DS . 'etc' . DS. 'Config.php';
						if(file_exists($_config)){
							$_config = include($_config);

							$vm = $this->vendor_name."_".$this->module_name;
							if(isset($_config['controllers'][$vm])){
								$_config['controllers'][$vm][] = $routerInfo;

								$data = '<?php' . PHP_EOL;
								$data .= 'return ' . var_export($_config, true) . PHP_EOL;
								$data .= '?>';

								$_writer = new \Of\File\Writer();
								$_writer->setDirPath(ROOT . DS . 'etc' . DS)
								->setData($data)
								->setFilename('Config')
								->setFileextension('php')
								->write();
							}
						}
						/** end insert into installation config */
						$response = [];
						$response['error'] = 0;
						$response['message'] = 'New conroller created.';
						$this->jsonEncode($response);
					}
				}
			} else {
				$this->returnError('406', 'Route already exist.');
			}
		}
	}

	/**
	 * this will create a controller Route/Controller/Action type
	 */
	private function createRCAController(){
		$route = $this->_request->getParam('controller_route');
		$conroller = $this->_request->getParam('controller_controller');
		$action = $this->_request->getParam('controller_action');
		$controller_type = $this->_request->getParam('controller_type');

		$crr = $this->_request->getParam('controller_route_regex');
		$ccr = $this->_request->getParam('controller_controller_regex');
		$car = $this->_request->getParam('controller_action_regex');

		$extends = $this->_request->getParam('extend_to_class');

		$sampleTemplate = 'sample_template.phtml';
		$isAdmin = false;

		if($controller_type == 'admin'){
			$sampleTemplate = 'admin/sample_template.phtml';
			$isAdmin = true;
		}

		if(!strlen($route)){
			$route = "Index";
		}
		if(!strlen($conroller)){
			$conroller = "Index";
		}
		if(!strlen($action)){
			$action = "Index";
		} 

		if($route == 'system' || $route == 'System'){
			$this->returnError('400', 'System route is reserved for Opoink\'s developer panel.');
		} else {
			$invalidPatternMsg = 'You will use regex for the {{path}}, but your pattern seems to be invalid. To avoid error please make sure your pattern is valid.';
			if($crr === 'yes'){
				if( preg_match("/^\/.+\/[a-z]*$/i", $route)) {
					$route = 'Reg'.sha1($route);
				} else {
					$this->returnError('400', str_replace('{{path}}', 'route', $invalidPatternMsg));
				}
			}
			if($ccr === 'yes'){;
				if( preg_match("/^\/.+\/[a-z]*$/i", $conroller)) {
					$conroller = 'Reg'.sha1($conroller);
				} else {
					$this->returnError('400', str_replace('{{path}}', 'conroller', $invalidPatternMsg));
				}
			}
			if($car === 'yes'){
				if( preg_match("/^\/.+\/[a-z]*$/i", $action)) {
					$action = 'Reg'.sha1($action);
				} else {
					$this->returnError('400', str_replace('{{path}}', 'action', $invalidPatternMsg));
				}
			}

			$conType = 'public';
			$xmlFilename = strtolower($route.'_'.$conroller.'_'.$action);
			$controllerClass = "\\".$this->vendor_name."\\".$this->module_name."\\Controller\\".ucfirst($route)."\\".ucfirst($conroller)."\\".ucfirst($action);

			if($controller_type == 'admin'){
				$conType = 'admin';
				$xmlFilename = 'admin_'.strtolower($route.'_'.$conroller.'_'.$action);
				$controllerClass =  "\\".$this->vendor_name."\\".$this->module_name."\\Controller\\Admin\\".ucfirst($route)."\\".ucfirst($conroller)."\\".ucfirst($action);
			}

			if(!empty($extends)){
				try {
					/* 
					 * this is to try if the class to extend exists
					 * if it is not injector will raise an error
					 */
					$this->_di->make($extends);
				} catch (\Exception $e) {
					$this->returnError('400', $e->getMessage());
				}

				$this->_controller->setExtends($extends);
			}

			$create = $this->_controller->setVendor($this->vendor_name)
			->setModule($this->module_name)
			->setRoute($route)
			->setController($conroller)
			->setAction($action)
			->create($conType);

			if($create){
				/** insert into module config */
				if($crr === 'yes' || $ccr === 'yes' || $car === 'yes'){
					$regexCount = 0;
					while (isset($this->config['controllers']['regex_'.$regexCount])) {
						$regexCount++;
					}
					$routerName = 'regex_'.$regexCount;
					$routerInfo = [
						'route' => $this->routerInfoHelper($crr, 'controller_route'),
						'route_regex' => $crr === 'yes' ? true : false,
						'controller' => $this->routerInfoHelper($crr, 'controller_controller'),
						'controller_regex' => $ccr === 'yes' ? true : false,
						'action' => $this->routerInfoHelper($crr, 'controller_action'),
						'action_regex' => $car === 'yes' ? true : false,
						'class' => $controllerClass
					];
				} else {
					$routerName = $xmlFilename;
					$routerInfo = $controllerClass;
				}

				$this->config['controllers'][$routerName] = $routerInfo;

				$this->_configManager->setConfig($this->config)
				->createConfig();
				/** end insert into module config */

				/** insert into installation config */
				$_config = ROOT . DS . 'etc' . DS. 'Config.php';
				if(file_exists($_config)){
					$_config = include($_config);

					$vm = $this->vendor_name."_".$this->module_name;
					if(isset($_config['controllers'][$vm])){
						$_config['controllers'][$vm][$routerName] = $routerInfo;

						$data = '<?php' . PHP_EOL;
						$data .= 'return ' . var_export($_config, true) . PHP_EOL;
						$data .= '?>';

						$_writer = new \Of\File\Writer();
						$_writer->setDirPath(ROOT . DS . 'etc' . DS)
						->setData($data)
						->setFilename('Config')
						->setFileextension('php')
						->write();
					}
				}
				/** end insert into installation config */

				/** create controller xml layout here */

				$body = "\t\t".'<container xml:id="main_container" htmlId="main_container" htmlClass="main_container" weight="1">' . PHP_EOL;
					$body .= "\t\t\t".'<template xml:id="sample_template" vendor="'.$this->vendor_name.'" module="'.$this->module_name.'" template="'.$sampleTemplate.'" cacheable="1" max-age="604800"/>' . PHP_EOL;
				$body .= "\t\t".'</container>' . PHP_EOL;

				$this->_xml->setVendor($this->vendor_name)
				->setModule($this->module_name)
				->setFileName($xmlFilename);
				if($isAdmin){
					$this->_xml->create(false, $isAdmin, 999, $body);
				} else {
					$this->_xml->create(true, $isAdmin, 999, $body);
				}
				/** end create controller xml layout here */

				/** create the sample template here */
				$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor_name.DS.$this->module_name.DS.'View'.DS.'Template';
				if($controller_type == 'admin'){
					$target .= DS.'admin';
				}

				$data = "<p>".$route." ".$conroller." ".$action." works</p>";
				$_writer = new \Of\File\Writer();
				$_writer->setDirPath($target)
				->setData($data)
				->setFilename('sample_template')
				->setFileextension('phtml')
				->write();
				/** end create the sample template here */

				$response = [];
				$response['error'] = 0;
				$response['message'] = 'New conroller created.';
				$this->jsonEncode($response);
			} else {
				$this->returnError('400', 'Cannot create, controller is already existing.');
			}
		}
	}


	private function routerInfoHelper($isRegex, $reqParam){
		$_reqParam = $this->_request->getParam($reqParam);

		$result = 'Index';
		if($_reqParam && $isRegex == 'yes'){
			$result = $_reqParam;
		}
		elseif(!$_reqParam && $isRegex == 'yes'){
			$result = 'Index';
		}
		elseif($_reqParam && $isRegex != 'yes'){
			$result = ucfirst($_reqParam);
		}
		return $result;
	}
}