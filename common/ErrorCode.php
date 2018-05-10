<?php

define ('EC_OK', 200);
define('EC_BAD_REQUEST', 400);
define('EC_SIGN_ERROR', 498);
define('EC_DATABASE_ERROR', 500);
define('EC_UPDATE_ERROR', 601);
define('EC_INVALID_PARAM', 602);
define('EC_USER_EXIST', 612);
define('EC_USER_NOT_EXIST', 620);
define('EC_USER_PWD_ERROR', 621);

//这几个错误码是给后台回调用的
define('EC_SYSTEM_INVALID_JSON_FORMAT', 4001);
define('EC_SYSTEM_INVALID_PARA', 4002);
define('EC_SYSTEM_FREQUECY',4003);


define('LOGIN_EXPIRED_TIME',1*24*60*60);

function genErrMsg($errCode, $errorMsg = "")
{
	$errMsg = array(
		EC_OK=>"OK",
        EC_BAD_REQUEST => "Bad Request
        EC_SIGN_ERROR => "Invalid Token",
        EC_DATABASE_ERROR => "Internal Server Error(db error)",
        EC_UPDATE_ERROR => "update error",
        EC_INVALID_PARAM => "invalid param",
        EC_USER_EXIST => "user exist",
        EC_USER_NOT_EXIST => "user not exist",
        EC_USER_PWD_ERROR => "password error",
		EC_SYSTEM_INVALID_JSON_FORMAT => "http post body is empty or invalid json format in post body from client!",
		EC_SYSTEM_INVALID_PARA => "request para error",
		EC_SYSTEM_FREQUECY => "frequency control"
		
	);
	
	if($errorMsg == "")
	{
		return $errMsg[$errCode];
	}
	
	return $errMsg[$errCode] . " | " . $errorMsg;
}

?>
