<?php

require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../common/GlobalFunctions.php';


class getsig extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid' => array('type' => 'string'),
            'type'=>array('type' => 'int', 'range' => '[0,+)'), //1:直播签名，2:cos签名，3:点播签名
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK,"getsig args=" . var_export($this->_args, true));
        $type = $this->_args['type'];
        $userid = $this->_args['userid'];
        $sigData = array();

        switch ($type) {
            case 1:
                GetLiveSign($userid, $sigData);
                break;
            case 2:
                GetCOSSign($sigData);
                break;
            case 3:
                GetVodSign($sigData);
                break;
            default:break;
        }

        $this->_retValue = EC_OK;
        $this->_data = $sigData;

        interface_log(INFO, EC_OK, 'getsig::process() succeed ');
        return true;
    }
}