<?php

require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class get_vod_list extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'index'=>array('type' => 'int', 'range' => '[0,+)'),
            'count' => array('type' => 'int', 'range' => '[0,+)')
        );
        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK,"get_vod_list args=" . var_export($this->_args, true));

        $config = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $index = 1;
        $count = 10;
        if(array_key_exists('index', $this->_args) && (int)$this->_args['index'] > 0)
        {
            $index = (int)$this->_args['index'];
        }
        if(array_key_exists('count', $this->_args) && (int)$this->_args['count'] >= 10 && (int)$this->_args['count'] <= 100)
        {
            $count = (int)$this->_args['count'];
        }

        $error_message = "";
        $ret = 0;

        $all_count =0;
        $result_list = array();
        $start_pos = ($index -1) * ($count);

        $ret = $dao_live->getTapeCount($all_count, $error_message);
        if($ret == 0) {
            $ret = $dao_live->getTapeList($start_pos, $count,$result_list, $error_message);
        }

        if($ret != 0)
        {
            $this->_retValue = EC_DATABASE_ERROR;
            return false;
        }

        $this->_retValue = EC_OK;
        $this->_data = array('list' => $result_list);

        interface_log(INFO, EC_OK, 'get_vod_list::process() succeed');
        return true;
    }
}