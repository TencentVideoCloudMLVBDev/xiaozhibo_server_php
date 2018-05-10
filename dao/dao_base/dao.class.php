<?php


require_once dirname(__FILE__) . '/../../common/ErrorCode.php';
require_once('simple_mysql_session.class.php');
require_once('util.php');
require_once dirname(__FILE__).'/../../common/Common.php';

class Dao
{
   
    const ERROR_CODE_SUCCESSFUL = 0;        // 成功
    const ERROR_CODE_DB_ERROR = 2003;          // 数据库错误
    const ERROR_CODE_SYSTEM_ERROR = 2;      // 系统错误
    const ERROR_CODE_DB_NO_RECORD = 3;

   
    
    // 转码格式类型
    const TRANSCODING_FORMAT_TYPE_OPTIONAL = 0;     // 可选
    const TRANSCODING_FORMAT_TYPE_REQUIRED = 1;     // 必选
    
    // mysql错误码
    const MYSQL_ERROR_FOREIGN_KEY_CONSTRAINT = 1452;      // 外键冲突
    const MYSQL_ERROR_DUPLICATE_KEY = 1062;               // 键重复
    
     function __construct($db_host,
                         $db_port,
                         $db_user,
                         $db_password,
                         $db_name)
    {
        date_default_timezone_set('PRC');
        $this->task_db_name = $db_name.'_task';
        $this->data_db_name = $db_name.'_data';
        $this->index_db_name = $db_name.'_index';
        $this->session_ = new SimpleMysqlSession($db_host, $db_port, $db_user,
            $db_password, $db_name);
    }

    

}

?>
