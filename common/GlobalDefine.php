<?php
//require_once dirname(__FILE__).'/ErrorCode.php';
define('CONFIG_FILE', dirname(dirname(__FILE__)) . '/conf/cdn.inc.ini');
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('COMPONENT_VERSION', '2.0'); //组件版本

define('TAPE_FILE_VALID_TIME', 604800); //获取回放列表的时间窗：当前时间（单位s）-TAPE_FILE_VALID_TIME ～ 当前时间。默认设置7天 7*24*60*60 = 604800
define('TAPE_DURATION_TIME', 60); //超过TAPE_DURATION_TIME的录制文件才会落数据库，单位s

error_reporting(E_ALL ^ E_NOTICE);
