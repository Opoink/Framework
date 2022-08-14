<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

use Of\Constants;

class File extends Filecontroller {

	/**
	 * \Of\Config
	 */
	protected $_config;

	/**
	 * \Of\Http\Url
	 */
	protected $_url;

	public function __construct(
		\Of\Config $Config,
		\Of\Http\Url $Url
	){
		$this->_config = $Config;
		$this->_url = $Url;
	}

	public function run($file){
		$path = $this->getPath($file);
		if($path){
			$realPath = $this->getRealPath($path, $file);

			if(is_string($realPath)){
				$this->beforeRenderFile($realPath, $file, $path);
			}
			else if(is_array($realPath)) {
				foreach ($realPath as $key => $_realPath) {
					$this->beforeRenderFile($_realPath, $file, $path);
				}
			}
		}
	}

	protected function beforeRenderFile($realPath, $file, $path) {
			
		$targetFile = $realPath.$file;
		$destinationFile = ROOT.$path.DS.$file;

		if(file_exists($targetFile)){
			if($this->_config->getConfig('mode') == Constants::MODE_PROD){
				$this->makeDir(ROOT.$path);
				copy($targetFile, $destinationFile);
			}
			
			$this->renderFile($targetFile, $file);
		}
	}
}
?>