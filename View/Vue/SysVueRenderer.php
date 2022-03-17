<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\View\Vue;

use MatthiasMullie\Minify;

class SysVueRenderer {

	const REWRITEVUECOMPONENTJS = true;
	const COMPONENT_DIR = ROOT . DS . 'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'vue'.DS.'components';
	const SERVICE_DIR = ROOT . DS . 'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'vue'.DS.'services';

	/**
	 * string contains the component JS
	 */
	protected $components = '';

	protected $services = [];
	protected $_fileWriter;

	/** this will be the location of the vue compent taht will be generated */
	protected $targetDir = '';
	protected $targetFilename = '';
	protected $targetFilenameExt = '';

	protected $_config;

	public function __construct(
		\Of\File\Writer $Writer,
		\Of\Config $Config
	){
		$this->_fileWriter = $Writer;
		$this->targetDir = ROOT . DS . 'Var' . DS . 'Sys' . DS . 'Js';
		$this->targetFilename = 'sysvuecomponents';
		$this->targetFilenameExt = 'js';
		$this->_config = $Config;
	}

	/**
	 * get all components for systems ui
	 * will scan DIR inside framework/View/Sys/vue/components
	 * each directory inside components are all the coponents used 
	 * in systems UI
	 */
	public function getComponents(){
		$target = $this->targetDir . DS . $this->targetFilename . '.' . $this->targetFilenameExt;
		if(!self::REWRITEVUECOMPONENTJS && !file_exists($target)){
			$this->getComponentsHelper();
		}
		elseif(self::REWRITEVUECOMPONENTJS) {
			$this->getComponentsHelper();
		}
		return $this;
	}

	protected function getComponentsHelper(){
		$components = scandir(self::COMPONENT_DIR);

		foreach ($components as $key => $componentName) {
			if($componentName == '.' || $componentName == '..'){
				continue;
			}
			$this->setComponent($componentName);
		}
	}

	/**
	 * set the component
	 * @param $componentName the name of the component is the name of 
	 * DIR inside framework/View/Sys/vue/components
	 */
	public function setComponent($componentName){

		$component = $this->getFile($componentName, $componentName . '-component.js');
		$template = $this->getTemplate($componentName);
		$datajs = $this->getFile($componentName, $componentName . '-data.js');

		if($component && $template){
			$component = str_replace("{{template}}", $template, $component);
			$this->components .= $component;
		}
	}

	/**
	 * get the file inside the component
	 * return file_get_contents if the file exists
	 * else return null
	 */
	protected function getFile($component, $fileName){
		$target = self::COMPONENT_DIR . DS . $component . DS . $fileName;
		if(file_exists($target)){
			return file_get_contents($target);
		}
	}

	/**
	 * get the template for the component
	 * and return the minified html content
	 */
	protected function getTemplate($component){
		$tpl = $this->getFile($component, $component . '-template.html');
		if($tpl){
			return $this->minifyTemplate($tpl);
		}
		else {
			return 'Template not found';
		}
	}

	/**
	 * get all services that is used by the components
	 */
	// public function getSirvices(){
	// 	$services = scandir(self::SERVICE_DIR);
	// 	foreach ($services as $key => $service) {
	// 		if($service == '.' || $service == '..'){
	// 			continue;
	// 		}
	// 		$s = explode('.', $service);
	// 		$lastKey = count($s) - 1;
	// 		if(isset($s[$lastKey])){
	// 			unset($s[$lastKey]);
	// 		}
	// 		$serviceName = implode('.', $s);

	// 		$target = self::SERVICE_DIR .'/' . $service;

	// 		$this->services[$serviceName] = file_get_contents($target);
	// 	}
	// }

	/**
	 * nimify the template remove all unnecessary whitespaces and remove comments
	 */
	function minifyTemplate($buffer) {
	    $search = array(
	        '/\>[^\S ]+/s',     /** strip whitespaces after tags, except space */
	        '/[^\S ]+\</s',     /** strip whitespaces before tags, except space */ 
	        '/(\s)+/s',         /** shorten multiple whitespace sequences */
	        '/<!--(.|\s)*?-->/' /** Remove HTML comments */
	    );

	    $replace = array(
	        '>',
	        '<',
	        '\\1',
	        ''
	    );

	    $buffer = preg_replace($search, $replace, $buffer);
	    $buffer = str_replace("'", "\\'", $buffer);
	    return $buffer;
	}

	/**
	 * merge all the component fetched
	 * and return minified js content
	 */
	public function toJs(){
		$main = '';
		$main .= 'var sysUrl = "'.\Of\Constants::BASE_SYS_URL.$this->_config->getConfig('system_url').'";';
		$mainTarget = dirname(self::COMPONENT_DIR) . DS . 'main.js';
		if(file_exists($mainTarget)){
			$main .= file_get_contents($mainTarget);
		}
		return $main . ' ' . $this->components;


		// $target = $this->targetDir . DS . $this->targetFilename . '.' . $this->targetFilenameExt;

		// if(!self::REWRITEVUECOMPONENTJS){
		// 	if(file_exists($target)){
		// 		return file_get_contents($target);
		// 	}
		// }
		// $minifier = new Minify\JS();

		// $Vue = '';
		// $data = [];

		// foreach ($this->services as $key => $service) {
		// 	$Vue .= $service . ' ' . PHP_EOL;
		// 	$data[] = $key . ': new ' . $key . '()';
		// }

		// foreach ($this->components as $key => $component) {
		// 	$minifier->add($component['component']);
		// 	$Vue .= $component['global_data'] . ' ' . PHP_EOL;
		// 	$data[] = $component['name'] . ': new ' . $component['name'] . '()';
		// }
		// $data = implode(',', $data);

		// $Vue .= "var _vue = new Vue({
		// 	el: '#root',
		// 	data: {
		// 		".$data."
		// 	},
		// 	beforeMount(){
		// 	},
		// 	mounted(){
		// 		let vCheck = setInterval(f => {
		// 			if(_vue){
		// 				let page = this.router.init();
		// 				if(typeof this[page] != 'undefined'){
		// 					if(typeof this[page].init === 'function') {
		// 						this[page].init();
		// 					}
		// 				}
		// 				clearInterval(vCheck);
		// 			}
		// 		}, 100)
		// 	}
		// })";
		// $minifier->add($Vue);

		// $js = $minifier->minify();

		// $this->_fileWriter->setDirPath($this->targetDir)
		// ->setData($js)
		// ->setFilename($this->targetFilename)
		// ->setFileextension($this->targetFilenameExt)
		// ->write();

		// return $js;
	}
}
?>