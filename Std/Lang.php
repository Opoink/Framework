<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Lang {

	protected $languages = [];

	/**
	 * \Of\Config
	 */
	protected $_config;

	public function __construct(
		\Of\Config $Config
	){
		$this->_config = $Config;
		$this->setLanguages();
	}

	/**
	 * this will set all languages from the installed module
	 * this will check for the json files inside App/Ext/Vendor/Module/languages
	 * in this DIR we will assume that all json file here are for languages only
	 */
	public function setLanguages(){
		$vendors = $this->_config->getConfig('modules');

		foreach ($vendors as $vendor => $modules) {
			foreach ($modules as $module) {
				$targetDir = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'languages';

				if(is_dir($targetDir)){
					$files = scandir($targetDir);

					foreach ($files as $file) {
						if ($file != "." && $file != "..") {
							$filePath = $targetDir.DS.$file;
							$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
							if($ext == 'json'){
								$fInfo = pathinfo($filePath);
								$fName = strtolower($fInfo['filename']);
		
								$fileContents = file_get_contents($filePath);
								$fileContents = json_decode($fileContents, true);
		
								if(array_key_exists($fName, $this->languages)){
									$this->languages[$fName] = array_merge($this->languages[$fName], $fileContents);
								} else {
									$this->languages[$fName] = $fileContents;
								}
							}
						}
					}
				}
			}
		}
	}

	public function getLang($key, $language, $values = null){
		if( array_key_exists($language, $this->languages) ){

			if (array_key_exists($key, $this->languages[$language]) ){
				if($values){
					$l = $this->languages[$language][$key];
					foreach ($values as $key => $value) {
						$l = str_replace("{{" . $value['key'] . "}}", $value['value'], $l);
					}
					return $l;
				} else {
					return $this->languages[$language][$key];
				}
			} else {
				return 'undefined text: ' . $key ;
			}
		} else {
			return 'undefined language: ' . $language . ' - ' . $key;
		}
	}
}
?>