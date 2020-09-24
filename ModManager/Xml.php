<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
* 
*/
namespace Of\ModManager;

class Xml {

	protected $config = [
		'vendor' => '',
		'module' => '',
		'filename' => '',
	];

	/**
	 * set the vendor name
	 */
	public function setVendor($vendor){
		$this->config['vendor'] = $vendor;
		return $this;
	}

	/**
	 * set the module name
	 */
	public function setModule($module){
		$this->config['module'] = $module;
		return $this;
	}

	/**
	 * set the file name of the xml to be created
	 * the filename's extension is not included
	 */
	public function setFileName($filename){
		$this->config['filename'] = $filename;
		return $this;
	}

	/**
	 * start the creation of the xml file
	 */
	public function create($inPublic=true, $inAdmn=true, $weight=1, $body=''){
		$data = "<?xml version=\"1.0\"?>" . PHP_EOL;
		$data .= "<html xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xml:id=\"html\" attr=\"lang='en-US'\" weight=\"".$weight."\">" . PHP_EOL;
			$data .= "\t<head xml:id=\"head\" weight=\"1\">" . PHP_EOL . PHP_EOL;
			$data .= "\t</head>" . PHP_EOL;
			$data .= "\t<body xml:id=\"body\" weight=\"1\">" . PHP_EOL . PHP_EOL;
				if($body){
					$data .= $body;
				}
			$data .= "\t</body>" . PHP_EOL;
		$data .= "</html>" . PHP_EOL;

		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->config['vendor'].DS.$this->config['module'].DS.'View'.DS.'Layout';

		if($inPublic){
			$this->createHelper($data, false);
		}
		if($inAdmn){
			$this->createHelper($data, true);
		}
	}

	public function createHelper($data, $admin=false){
		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->config['vendor'].DS.$this->config['module'].DS.'View'.DS.'Layout';
		if($admin){
			$target .= DS.'Admin';
		}

		$_writer = new \Of\File\Writer();
		$_writer->setDirPath($target)
		->setData($data)
		->setFilename($this->config['filename'])
		->setFileextension('xml')
		->write();
	}
}
?>