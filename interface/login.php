<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';
require_once dirname(__FILE__) . '/../dao/redis_cache.php';
require_once dirname(__FILE__) . '/../common/TLSSigAPIv2.php';

class login extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid'   => array('type' => 'string', 'reg' => '^[a-zA-Z][a-zA-Z0-9_]{3,23}$'),
            'password' => array('type' => 'string', 'reg' => '^[a-zA-Z0-9]+$'),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return true;
    }

    private function error_resp($retCode, $retMsg)
    {
        $this->_retValue = $retCode;
        $this->_retMsg   = 'login fail: ' . $retMsg;
        interface_log(ERROR, $retCode, $this->_retMsg);
        return false;
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "login args=" . var_export($this->_args, true));

        $config   = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $userid   = $this->_args['userid'];
        $password = "";
        //..
        $ret = $dao_live->getAccountRecord($userid, $password);
        if ($ret != 0) {
            if ($ret == 3) {
                return $this->error_resp(EC_USER_NOT_EXIST, $userid . genErrMsg(EC_USER_NOT_EXIST));
            } else {
                return $this->error_resp(EC_DATABASE_ERROR, $userid . genErrMsg(EC_DATABASE_ERROR));
            }
        }

        if (strcmp($password, $this->_args['password']) != 0) {
            return $this->error_resp(EC_USER_PWD_ERROR, $userid . genErrMsg(EC_USER_PWD_ERROR));
        }

        $this->_retValue = EC_OK;

        $token        = "" . rand();
        $refreshToken = "" . rand();
        $session      = array('token' => $token, 'refresh_token' => $refreshToken, 'expires' => LOGIN_EXPIRED_TIME);
        redis_cache::instance()->set($userid, $session);

        $api             = new \Tencent\TLSSigAPIv2(IM_SDKAPPID, IM_SECRETKEY);
        $userSig         = $api->genSig($userid);
        $roomservicesign = array("sdkAppID" => IM_SDKAPPID, "userID" => $userid, "userSig" => $userSig);

        $cosinfo = array('Bucket' => COSKEY_BUCKET, 'Region' => COSKEY_BUCKET_REGION, 'Appid' => COSKEY_APPID, 'SecretId' => COSKEY_SECRECTID);

        $this->_data = array('token' => $token, 'refresh_token' => $refreshToken, 'expires' => LOGIN_EXPIRED_TIME, 'roomservice_sign' => $roomservicesign, 'cos_info' => $cosinfo);
        interface_log(INFO, EC_OK, 'login::process() succeed ');
        return true;
    }
}
