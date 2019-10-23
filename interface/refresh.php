<?php

require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/redis_cache.php';

class refresh extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid'        => array('type' => 'string', 'reg' => '^[a-zA-Z][a-zA-Z0-9_]{3,23}$'),
            'refresh_token' => array('type' => 'string'),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "refresh args=" . var_export($this->_args, true));

        $userid = $this->_args['userid'];

        $oldSession = redis_cache::instance()->get($userid);

        if ($oldSession && strcmp($oldSession['refresh_token'], $this->_args['refresh_token']) == 0) {
            $token        = "" . rand();
            $refreshToken = "" . rand();
            $session      = array('token' => $token, 'refresh_token' => $refreshToken, 'expires' => LOGIN_EXPIRED_TIME);
            redis_cache::instance()->set($userid, $session);
            $this->_retValue = EC_OK;
            $this->_data     = $session;
            interface_log(INFO, EC_OK, 'refresh::process() succeed ');
            return true;
        } else {
            $this->_retValue = EC_SIGN_ERROR;
            return false;
        }
    }
}
