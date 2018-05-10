<?php

require_once dirname(__FILE__) . '/../common/Common.php';

class get_roomservice_sign extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid' => array('type' => 'string'),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "get_roomservice_sign args=" . var_export($this->_args, true));
        $userid = $this->_args['userid'];

        $current = time();
        $expired = $current + 10 * 60;
        $sign = md5(API_KEY . strval($expired) . $userid);

        $this->_retValue = EC_OK;
        $this->_data = array("txTime" => $expired, "sign" => $sign);

        interface_log(INFO, EC_OK, 'get_roomservice_sign::process() succeed ');
        return true;
    }
}