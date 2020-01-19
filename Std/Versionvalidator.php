<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Versionvalidator extends Versioncompare {
	
	public function validate($version){
		$versions = explode('.', $version);

		if(count($versions) == 3){
			$isValid = true;
			foreach ($versions as $key => $value) {
				if(!preg_match('/^[0-9]+$/', $value)){
					$isValid = false;
				}
			}
			return $isValid;
		}
		return false;
	}
}