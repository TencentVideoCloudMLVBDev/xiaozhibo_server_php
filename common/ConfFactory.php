<?php

include_once dirname(__FILE__).'/Ini.php';
include_once dirname(__FILE__).'/GlobalDefine.php';
class ConfFactory
{
	private static $_version = '0.0.2';

	public static $conf = array();
	
	static public function getVersion()
	{
		return self::$_version;
	}		
	
	 public static function getInstance($type = 'ini')
	{
		if(!empty(ConfFactory::$conf))
		{
			return ConfFactory::$conf;
		}
		$oObj = new Ini();
		ConfFactory::$conf = $oObj->loadInc(CONFIG_FILE);
		return ConfFactory::$conf;
	}
}