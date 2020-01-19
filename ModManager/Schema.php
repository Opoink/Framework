<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
* 
*/
namespace Of\ModManager;

class Schema {

	protected $vendor;
	protected $module;

	/**
	 *	create the install.php and upgrade.php class
	 */
	public function create($vendor_name, $module_name){
		$this->vendor = $vendor_name;
		$this->module = $module_name;

		$this->createInstall();
		$this->upgradeInstall();
	}

	/**
	 *	add the install schema .php
	 *	into the module to be installed
	 */
	protected function createInstall(){
		$data = '<?php' . PHP_EOL;
		$data .= 'namespace '.$this->vendor.'\\'.$this->module.'\\Schema;' . PHP_EOL . PHP_EOL;
		$data .= 'class Install extends \Of\Db\Createtable {' . PHP_EOL . PHP_EOL;
		$data .= "\tpublic function createSchema(){" . PHP_EOL . PHP_EOL;
		$data .= "\t}" . PHP_EOL;
		$data .= '}' . PHP_EOL;
		$data .= '?>';

		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor.DS.$this->module.DS.'Schema';

		$_writer = new \Of\File\Writer();
		$_writer->setDirPath($target)
		->setData($data)
		->setFilename('Install')
		->setFileextension('php')
		->write();
	}

	/**
	 *	add the Upgrade schema .php
	 *	into the module to be installed
	 */
	protected function upgradeInstall(){
		$data = '<?php' . PHP_EOL;
		$data .= 'namespace '.$this->vendor.'\\'.$this->module.'\\Schema;' . PHP_EOL . PHP_EOL;
		$data .= 'class Upgrade extends \Of\Db\Createtable {' . PHP_EOL . PHP_EOL;
		$data .= "\tpublic function upgradeSchema(\$currentVersion, \$newVersion){" . PHP_EOL . PHP_EOL;
		$data .= "\t}" . PHP_EOL;
		$data .= '}' . PHP_EOL;
		$data .= '?>';

		$target = ROOT.DS.'App'.DS.'Ext'.DS.$this->vendor.DS.$this->module.DS.'Schema';

		$_writer = new \Of\File\Writer();
		$_writer->setDirPath($target)
		->setData($data)
		->setFilename('Upgrade')
		->setFileextension('php')
		->write();
	}
}