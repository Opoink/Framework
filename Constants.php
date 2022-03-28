<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of;

class Constants {
	
	const MODE_DEV = 'developer';
	const MODE_PROD = 'production';
	
	/*
	*	public generation time
	*	use for static generated files like CSS JS, etc
	*/
	const GENERATION_TIME = ROOT.DS.'public'.DS.'generation.php';
	
	/* form key duration in sec */
	const FORM_KEY_DURATION = 30;

	const BASE_SYS_URL = 'system';

	
	const EXT_DIR = ROOT.DS.'App'.DS.'Ext';

	/** 
	 * the below constansts are the DIR inside the module
	 */
	const MODULE_DB_SCHEMA_DIR = DS.'Schema';
	const MODULE_DB_TABLES_DIR = DS.'Schema'.DS.'tables';
}
?>