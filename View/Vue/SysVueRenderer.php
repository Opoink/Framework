<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\View\Vue;

use MatthiasMullie\Minify;

class SysVueRenderer {


	const COMPONENT_DIR = ROOT . '/vendor/opoink/framework/View/Sys/vue/components';
	protected $components = [];

	public function getComponents(){
		$components = scandir(self::COMPONENT_DIR);
		foreach ($components as $key => $componentName) {
			if($componentName == '.' || $componentName == '..'){
				continue;
			}
			$this->setComponent($componentName);
		}
		return $this;
	}

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

			// $js = "Vue.component('".$component."', {";
			// $js .= $data.",";
			// $js .= "template: '".$_template."'";
			// $js .= "});";
			// $js .= "new Vue({ el: '.vue-comp-".$component."' });";


			// $js = "//component A
			// Vue.component('my-button', {
			//   props: ['counter'],
			//   template: `<button v-on:click=\"\$emit('add-value')\">You clicked me {{ counter }} times.</button>`
			// });

			// new Vue({
			//   el: '.vue-comp-schema',
			//   data: {
			//   	counter: 0
			//   },
			//   methods: {
			// doSomething: function() {
			// 	this.counter++;
			// }
			// }
			// })";
		}
	}

	protected function getFile($component, $fileName){
		$target = self::COMPONENT_DIR . '/'.$component.'/'.$fileName;
		if(file_exists($target)){
			return file_get_contents($target);
		}
	}

	protected function getTemplate($component){
		$tpl = $this->getFile($component, 'template.html');
		if($tpl){
			return $this->minifyTemplate($tpl);
		}
	}

	function minifyTemplate($buffer) {
	    $search = array(
	        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
	        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
	        '/(\s)+/s',         // shorten multiple whitespace sequences
	        '/<!--(.|\s)*?-->/' // Remove HTML comments
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

	public function toJs(){
		$minifier = new Minify\JS();

		$data = [];
		foreach ($this->components as $key => $component) {
			$minifier->add($component['component']);
			$data[] = $component['global_data'];
		}
		$data = implode(',', $data);

		$Vue = "new Vue({
			el: '#main-wrapper',
			data: {
				".$data."
			}
		})";
		$minifier->add($Vue);

		$js = $minifier->minify();
		return $js;
	}
}
?>