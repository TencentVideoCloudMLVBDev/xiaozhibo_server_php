<?php

require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class get_user_info extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid' => array('type' => 'string', 'reg' => '^[a-zA-Z][a-zA-Z0-9_]{3,23}$'),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "get_user_info args=" . var_export($this->_args, true));

        $config   = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $userid = $this->_args['userid'];
        $ret    = $dao_live->getUserInfo($userid, $userinfo);
        if ($ret == 0) {
            $this->_retValue = EC_OK;
            $this->_data     = array('userid' => $userinfo['userid'],
                'nickname'                    => $userinfo['nickname'] === null ? "" : $userinfo['nickname'],
                'avatar'                      => $userinfo['avatar'] === null ? "" : $userinfo['avatar'],
                'sex'                         => intval($userinfo['sex']),
                'frontcover'                  => $userinfo['frontcover'] === null ? "" : $userinfo['frontcover']);
        } else if ($ret == 3) {
            $this->_retValue = EC_USER_NOT_EXIST;
        } else {
            $this->_retValue = EC_DATABASE_ERROR;
        }

        interface_log(INFO, EC_OK, 'get_user_info::process() succeed ');
        return true;
    }
}
