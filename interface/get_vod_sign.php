<?php
require_once dirname(__FILE__) . '/../common/Common.php';

class get_vod_sign extends AbstractInterface
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
        interface_log(INFO, EC_OK, "get_vod_sign args=" . var_export($this->_args, true));

        $current = time();
        $expired = $current + 86400;
        $procedure = "XIAOZHIBO-DEFAULT";
        $arg_list = array(
            "secretId" => strval(CLOUD_API_SECRETID),
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "procedure" => $procedure,
            "random" => rand());

        $orignal = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $orignal, strval(CLOUD_API_SECRETKEY), true).$orignal);

        $this->_retValue = EC_OK;
        $this->_data = array("signature"=>$signature);

        interface_log(INFO, EC_OK, 'get_vod_sign::process() succeed ');
        return true;
    }
}