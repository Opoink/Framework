<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

use Of\Std\Versioncompare;

class SystemInstallRequirement extends Sys {
	
	protected $pageTitle = 'Requirement Opoink Framework';
	protected $phpVersion = '7.1';
	protected $memLimit = 128;
	
	public function run(){
		$this->requireNotInstalled();
		$toCheck = $this->_request->getParam('check');

		$data = [];
		$vc = new Versioncompare;

		if($toCheck == 'phpver'){
			$phpVer = phpversion();

			$passed = $vc->setCurrentVersion($this->phpVersion)
			->setNewVersion($phpVer)
			->compare();


			$data = [
				'phpversion' => $phpVer,
				'passed' => $passed == 1 ? true : false,
				'required' => $this->phpVersion,
				'message' => 'Required PHP version is ' . $this->phpVersion . '.x, Your PHP version is ' . $phpVer
			];
		}
		elseif($toCheck == 'memlimit') {
			$memLimit = (int)str_replace('m', '', strtolower(ini_get('memory_limit')) );
			$data = [
				'memory_limit' => $memLimit,
				'passed' => $memLimit >=  $this->memLimit ? true : false,
				'required' => $this->memLimit,
				'message' => 'Required Memory limit '.$this->memLimit.'M - Your PHP memory limit is '.$memLimit.'M'
			];
		}
		elseif($toCheck == 'writabledir') {
			$targetetc = ROOT.DS.'etc';
			$targetVar = ROOT.DS.'Var';

			if(is_writable($targetetc) && is_writable($targetVar)){
				$data = [
					'passed' => true,
					'message' => 'The directory etc and Var is writable'
				];
			} else {
				$data = [
					'passed' => false,
					'message' => 'Please make etc and Var writable by user id that the web server runs'
				];
			}
		}
		else {
			header("HTTP/1.0 400 Bad Request");
			echo "Invalid request";
			die;
		}

		$this->jsonEncode($data);
	}
	
}