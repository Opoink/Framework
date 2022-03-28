<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemDbAddForeignkey extends Sys {
	
	protected $pageTitle = 'Database Add Foreignkey';
	protected $_migrate;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Database\Migration\Migrate $Migrate
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_migrate = $Migrate;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();

		$validate = $this->validateFormKey();
		if($validate){
			$vendorModules = $this->getConfig('modules');

			foreach ($vendorModules as $vendor => $valueModules) {
				foreach ($valueModules as $keyModule => $module) {
					$targetFile = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'Schema'.DS.'relationship.json';
					if(file_exists($targetFile)){
						$data = file_get_contents($targetFile);
						$data = json_decode($data, true);

						if(json_last_error() == JSON_ERROR_NONE){
							foreach($data as $val){
								if(
									isset($val['tablename']) && 
									isset($val['constraint_name']) && 
									isset($val['on_delete']) &&
									isset($val['on_updated']) &&
									isset($val['column']) && 
									isset($val['reference_tablename']) && 
									isset($val['reference_column'])
								){
									try {
										$this->_migrate->addForeignKey(
											$val['tablename'], 
											$val['column'], 
											$val['reference_tablename'], 
											$val['reference_column'], 
											$val['on_delete'], 
											$val['on_updated'], 
											$val['constraint_name']
										);
									} catch (\Exception $e) {
										$this->returnError($e->getCode(), $e->getMessage() . ': ' . $targetFile);
									}
								} else {
									$this->returnError('406', 'Param tablename, constraint_name, on_delete, on_updated, column, reference_tablename, and  reference_column are required on file ' . $targetFile);
								}
							}
						} else {
							$this->returnError('406', json_last_error_msg () . ': asd ' . $targetFile);
						}
					}
				}
			}
			
			$this->jsonEncode([
				'error' => 0,
				'message' => 'All installed module database relationship has been executed.'				
			]);
		} else {
			$this->returnError('400', 'Invalid formkey request');
		}
	}
}
?>