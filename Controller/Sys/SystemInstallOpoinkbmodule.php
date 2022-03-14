<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

use Of\Std\Password;

class SystemInstallOpoinkbmodule extends Sys {
	
	protected $pageTitle = 'Install Opoink Bmodule';
	
	public function run(){
		$this->requireInstalled(false);
		$this->requireLogin(false);
		$response = [
			'error' => 1,
			'message' => ''
		];
		
		$validate = $this->validateFormKey();
		
		if($this->validateFormKey()){
			try {
				$target = ROOT.DS.'vendor'.DS.'opoink'.DS.'bmodule';
				$dst = ROOT.DS.'App'.DS.'Ext'.DS.'Opoink'.DS.'Bmodule';
				if(is_dir($target)){
					if(is_dir($target) && !is_dir($dst)){
						$dirmanager = $this->_di->get('Of\File\Dirmanager');
						$dirmanager->copyDir($target, $dst);
					}
				}

				if(is_dir($dst)){
					$modManager = $this->_di->get('\Of\ModManager\Module');

					$availableModules = [
						'Opoink_Bmodule'
					];
					$installed = $modManager->setDi($this->_di)->installModule($availableModules);
					$installedCount = count($installed);

					if($installedCount == 1){
						$response['error'] = 0;
						$response['message'] = 'Opoink/Bmodule successfully installed';
					} else {
						header("HTTP/1.0 406 Not Acceptable");
						echo 'Failed to install Opoink/Bmodule';
						die;
					}
				} else {
					header("HTTP/1.0 406 Not Acceptable");
					echo 'Opoink/Bmodule does not exist.';
					die;
				}

			} catch (\Exception $e) {
				header("HTTP/1.0 400 Bad Request");
				echo $e->getMessage();
				die;
			}
		} else {
			header("HTTP/1.0 400 Bad Request");
			echo 'Invalid request';
			die;
		}
		$this->jsonEncode($response);
	}
	

}