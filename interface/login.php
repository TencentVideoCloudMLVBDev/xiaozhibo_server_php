<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';
require_once dirname(__FILE__) . '/../dao/redis_cache.php';


class login extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid' => array('type' => 'string', 'reg' => '^[a-zA-Z][a-zA-Z0-9_]{3,23}$'),
            'password' => array('type' => 'string', 'reg' => '^[a-zA-Z0-9]+$')
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
        $this->_retMsg = 'login fail: ' . $retMsg;
        interface_log(ERROR, $retCode, $this->_retMsg);
        return false;
    }

    /* 功能：生成sig
     * 说明：对当前用户使用指定的sdkappid, 和指定的秘钥文件路径（private_key_path）使用signature工具生成sig
     * 参考文档 https://cloud.tencent.com/document/product/269/1510#2.5-php.E6.8E.A5.E5.8F.A3
    */
    public function genUserSig($sdkappid, $private_key_path,$userid)
    {
        // 这里需要写绝对路径，开发者根据自己的路径进行调整
        $command = DEPS_PATH . '/bin/signature'
            . ' ' . escapeshellarg($private_key_path)
            . ' ' . escapeshellarg($sdkappid)
            . ' ' . escapeshellarg($userid);
        $ret = exec($command, $out, $status);
        if ($status == -1)
        {
            return null;
        }
        return $out[0];
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "login args=" . var_export($this->_args, true));

        $config = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $userid = $this->_args['userid'];
        $password = "";
        //查重
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

        $token = "" . rand();
        $refreshToken = "" . rand();
        $session = array('token' => $token, 'refresh_token' => $refreshToken, 'expires' => LOGIN_EXPIRED_TIME);
        redis_cache::instance()->set($userid, $session);

        $private_key = DEPS_PATH . '/sig/private_key';
        $userSig = $this->genUserSig(IM_SDKAPPID, $private_key, $userid);
        $roomservicesign = array("sdkAppID" => IM_SDKAPPID, "accountType" => IM_ACCOUNTTYPE, "userID" => $userid, "userSig" => $userSig);

        $cosinfo = array('Bucket' => COSKEY_BUCKET, 'Region' => COSKEY_BUCKET_REGION, 'Appid' => COSKEY_APPID, 'SecretId' => COSKEY_SECRECTID);

        $this->_data = array('token' => $token, 'refresh_token' => $refreshToken, 'expires' => LOGIN_EXPIRED_TIME, 'roomservice_sign' => $roomservicesign, 'cos_info' => $cosinfo);
        interface_log(INFO, EC_OK, 'login::process() succeed ');
        return true;
    }
}

?>
