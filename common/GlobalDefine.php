<?php 
//require_once dirname(__FILE__).'/ErrorCode.php';
define('CONFIG_FILE', dirname(__FILE__).'/../conf/cdn.inc.ini');
define('ROOT_PATH', dirname(__FILE__) . '/../');
define('DEFAULT_CHARSET', 'utf-8');
define('COMPONENT_VERSION', '1.0');


define('VIEWER_COUNT',0);
define('LIKE_COUNT',1);
define('COUNT_ADD',0);
define('COUNT_DELETE',1);
define('GET_LIST_TYPE_ONLINE',1);
define('GET_LIST_TYPE_TAPE',2);
define('GET_LIST_TYPE_ALL',3);
define('GET_LIST_LIVE_DATA_ALL',4);
define('GET_LIST_UGC_DATA',5);

define('TAPE_FILE_VALID_TIME',604800);
define('TAPE_DURATION_TIME',60);

define('DEPS_PATH', ROOT_PATH . '/deps');



error_reporting(E_ALL ^ E_NOTICE);

?>
