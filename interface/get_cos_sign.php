<?php
require_once dirname(__FILE__) . '/../common/Common.php';

class get_cos_sign extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        return true;
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "get_cos_sign args=" . var_export($this->_args, true));

        $currentTime = time();
        $expiredTime = $currentTime + COSKEY_EXPIRED_TIME;
        $keyTime = $currentTime . ';' . $expiredTime;
        $signStr = bin2hex(hash_hmac('SHA1', $keyTime, COSKEY_SECRECTKEY, true));

        $this->_retValue = EC_OK;
        $this->_data = array("signKey"=>$signStr, "keyTime"=>$keyTime);

        interface_log(INFO, EC_OK, 'get_cos_sign::process() succeed ');
        return true;
    }
}