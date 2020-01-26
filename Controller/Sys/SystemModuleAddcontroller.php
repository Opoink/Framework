<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/

namespace Of\Controller\Sys;

class SystemModuleAddcontroller extends Sys {

	
	protected $pageTitle = 'Opoink Module Update';
	protected $_configManager;
	protected $_controller;
	protected $_validator;
	protected $_xml;

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
		$module_ver = $this->_request->getParam('module_ver');

		$vendor_name = ucfirst($this->_request->getParam('vendor_name'));
		$module_name = ucfirst($this->_request->getParam('module_name'));

		$redirectUrl = '/system/module/edit/mod/'.$vendor_name.'_'.$module_name;

		$config = $this->_validator->checkExist($vendor_name, $module_name);
		if($config){
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
				$this->_message->setMessage('System route is reserved for Opoink\'s developer panel.', 'danger');
			} else {

				if($crr === 'yes'){
					$route = 'Reg'.sha1($route);
				}
				if($ccr === 'yes'){
					$conroller = 'Reg'.sha1($conroller);
				}
				if($car === 'yes'){
					$action = 'Reg'.sha1($action);
				}

				$conType = 'public';
				$xmlFilename = strtolower($route.'_'.$conroller.'_'.$action);
				$controllerClass = "\\".$vendor_name."\\".$module_name."\\Controller\\".ucfirst($route)."\\".ucfirst($conroller)."\\".ucfirst($action);

				if($controller_type == 'admin'){
					$conType = 'admin';
					$xmlFilename = 'admin_'.strtolower($route.'_'.$conroller.'_'.$action);
					$controllerClass =  "\\".$vendor_name."\\".$module_name."\\Controller\\Admin\\".ucfirst($route)."\\".ucfirst($conroller)."\\".ucfirst($action);
				}

				if(!empty($extends)){
					try {
						/* 
						 * this is to try if the class to extend exists
						 * if it is not injectore will raise an error
						 */
					    $this->_di->make($extends);
					} catch (Exception $e) {
					    /* do nothing */
					}

					$this->_controller->setExtends($extends);
				}

				$create = $this->_controller->setVendor($vendor_name)
				->setModule($module_name)
				->setRoute($route)
				->setController($conroller)
				->setAction($action)
				->create($conType);

				if($create){
					/** insert into module config */
					if($crr === 'yes' || $ccr === 'yes' || $car === 'yes'){
						$regexCount = 0;
						while (isset($config['controllers']['regex_'.$regexCount])) {
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

					$config['controllers'][$routerName] = $routerInfo;

					$this->_configManager->setConfig($config)
					->createConfig();
					/** end insert into module config */

					/** insert into installation config */
					$_config = ROOT . DS . 'etc' . DS. 'Config.php';
					if(file_exists($_config)){
						$_config = include($_config);

						$vm = $vendor_name."_".$module_name;
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
						$body .= "\t\t\t".'<template xml:id="sample_template" vendor="'.$vendor_name.'" module="'.$module_name.'" template="'.$sampleTemplate.'" cacheable="1" max-age="604800"/>' . PHP_EOL;
					$body .= "\t\t".'</container>' . PHP_EOL;

					$this->_xml->setVendor($vendor_name)
					->setModule($module_name)
					->setFileName($xmlFilename)
					->create(true, $isAdmin, 999, $body);
					/** end create controller xml layout here */

					/** create the sample template here */
					$target = ROOT.DS.'App'.DS.'Ext'.DS.$vendor_name.DS.$module_name.DS.'View'.DS.'Template';
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
					$this->_message->setMessage('New conroller created.', 'success');
				} else {
					$this->_message->setMessage('Cannot create, controller is already existing.', 'danger');
				}
			}
		} else {
			$this->_message->setMessage('Module name is not existing.', 'danger');
		}

		$this->_url->redirectTo($this->getUrl($redirectUrl));
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