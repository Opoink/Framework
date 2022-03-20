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
	}
}
?>