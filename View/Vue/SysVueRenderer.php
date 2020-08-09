<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\View\Vue;

use MatthiasMullie\Minify;

class SysVueRenderer {


	const COMPONENT_DIR = ROOT . DS . 'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'vue'.DS.'components';
	const SERVICE_DIR = ROOT . DS . 'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'vue'.DS.'services';
	protected $components = [];
	protected $services = [];

	/**
	 * get all components for systems ui
	 * will scan DIR inside framework/View/Sys/vue/components
	 * each directory inside components are all the coponents used 
	 * in systems UI
	 */
	public function getComponents(){
		$this->getSirvices();

		$components = scandir(self::COMPONENT_DIR);
		foreach ($components as $key => $componentName) {
			if($componentName == '.' || $componentName == '..'){
				continue;
			}
			$this->setComponent($componentName);
		}
		return $this;
	}

	/**
	 * get all services that is used by the components
	 */
	public function getSirvices(){
		$services = scandir(self::SERVICE_DIR);
		foreach ($services as $key => $service) {
			if($service == '.' || $service == '..'){
				continue;
			}
			$s = explode('.', $service);
			$lastKey = count($s) - 1;
			if(isset($s[$lastKey])){
				unset($s[$lastKey]);
			}
			$serviceName = implode('.', $s);

			$target = self::SERVICE_DIR .'/' . $service;

			$this->services[$serviceName] = $serviceName . ': ' .file_get_contents($target);
		}
	}

	/**
	 * set the component
	 * @param $componentName the name of the component is the name of 
	 * DIR inside framework/View/Sys/vue/components
	 */
	public function setComponent($componentName){
		$this->setComponentHelper($componentName);
	}

	protected function setComponentHelper($componentName){
		$component = $this->getFile($componentName, 'component.js');
		$template = $this->getTemplate($componentName);

		if($component && $template){
			$component = str_replace("{{template}}", $template, $component);
			$this->components[$componentName] = [
				'component' => $component,
				'global_data' => $componentName.": ".$this->getFile($componentName, 'data.js')
			];
		}
	}

	/**
	 * get the file inside the component
	 * return file_get_contents if the file exists
	 * else return null
	 */
	protected function getFile($component, $fileName){
		$target = self::COMPONENT_DIR . '/'.$component.'/'.$fileName;
		if(file_exists($target)){
			return file_get_contents($target);
		}
	}

	/**
	 * get the template for the component
	 * and return the minified html content
	 */
	protected function getTemplate($component){
		$tpl = $this->getFile($component, 'template.html');
		if($tpl){
			return $this->minifyTemplate($tpl);
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
		$minifier = new Minify\JS();

		$data = [];
		foreach ($this->services as $key => $service) {
			$data[] = $service;
		}

		foreach ($this->components as $key => $component) {
			$minifier->add($component['component']);
			$data[] = $component['global_data'];
		}
		$data = implode(',', $data);

		$Vue = "var _vue = new Vue({
			el: '#root',
			data: {
				".$data."
			},
			beforeMount(){
		    },
		    mounted(){
		    	let vCheck = setInterval(f => {
		    		if(_vue){
						let page = this.router.init();
						if(typeof this[page] != 'undefined'){
							if(typeof this[page].init === 'function') {
								this[page].init();
							}
						}
	    				clearInterval(vCheck);
		    		}
	    		}, 100)
		    }
		})";
		$minifier->add($Vue);

		$js = $minifier->minify();
		return $js;
	}
}
?>