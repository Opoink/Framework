<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\File;

use ScssPhp\ScssPhp\Compiler;

/* https://www.minifier.org/ */
use MatthiasMullie\Minify;

class ScssBuilder extends \Of\Less\Builder {

	protected $filePaths = [];
	protected $_config;
	

	public function setConfig($Config){
		$this->_config = $Config;
		return $this;
	}

	public function build($file) {
		$requestUri = $_SERVER['REQUEST_URI'];
		$uri = explode('?', $requestUri);

		$cssString = '';
		if($uri[0] != '' || $uri[0] != null){

			$_file = str_replace('.scss.css', '.scss', $file);

			$path = str_replace('/'.$file, '', $uri[0]);
			$path = strtolower(str_replace('/', DS, $path));

			$path =  $this->getRealPath($path);

			$_scssFiles = [];
			foreach($this->_config->getConfig('modules') as $vendor => $modules) {
				foreach($modules as $module){
					$viewDir = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS;
					$scssFile = ltrim(strtolower($path.DS.$_file), DS);

					$targetScssFile = $viewDir . $scssFile;

					if(file_exists($targetScssFile)){
						$_scssFiles[] = $targetScssFile;
					}
				}
			}

			foreach ($_scssFiles as $key => $scssFile) {
				$compiler = new Compiler();
				$compiler->setImportPaths(dirname($scssFile));

				$scssString = file_get_contents($scssFile);
				$cssString .= $compiler->compileString($scssString)->getCss();
			}

			if($this->_config->getConfig('mode') == \Of\Constants::MODE_PROD){
				$minifier = new Minify\CSS();
				$minifier->add($cssString);

				$deploy = ROOT.DS.'public'.DS.'deploy';
				if(file_exists(ROOT.DS.'public'.DS.'generation.php')){
					$deploy .= include(ROOT.DS.'public'.DS.'generation.php');
				}
	
				$publicCssDir = $deploy . DS . ltrim($path, DS);
				$destinationFile = $publicCssDir.DS.$file;

				$this->makeDir($publicCssDir);
				return $minifier->minify($destinationFile);
			} else {
				return $cssString;
			}
		}
		else {
			return $cssString;
		}
	}
}
?>