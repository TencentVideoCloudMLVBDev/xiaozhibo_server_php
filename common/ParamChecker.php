<?php
include_once dirname(__FILE__) . '/Param.php';

class ParamChecker {
	private static $param;
	
	public static function getInstance () {
		if (null == self::$param) {
			self::$param = new Param();
		}
		
		return self::$param;
	}
}

?>
