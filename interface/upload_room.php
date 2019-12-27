<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class upload_room extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $rules = array(
            'userid'     => array('type' => 'string', 'reg' => '^[a-zA-Z][a-zA-Z0-9_]{3,23}$'),
            'title'      => array('type' => 'string'),
            'frontcover' => array('type' => 'string', "nullable" => true, "emptyable" => true),
            'location'   => array('type' => 'string', "nullable" => true, "emptyable" => true),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function verifySign(&$args, $Sign)
    {
        return $this->_verifySign($args, $Sign);
    }

    public function process()
    {
        interface_log(INFO, EC_OK, "upload_room args=" . var_export($this->_args, true));

        $config   = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $inforet = $dao_live->getUserInfo($this->_args['userid'], $userinfo);
        if ($inforet == ERROR_CODE_SUCCESSFUL) {
            $this->_args['frontcover'] = $userinfo['frontcover'] === null ? "" : $userinfo['frontcover'];
        }

        $ret = $dao_live->addRoom($this->_args['userid'],
            $this->_args['title'],
            $this->_args['frontcover'],
            $this->_args['location']);
        if ($ret == 0) {
            $this->_retValue = EC_OK;
        } else {
            $this->_retValue = EC_DATABASE_ERROR;
        }

        interface_log(INFO, EC_OK, 'upload_room::process() succeed ');
    }
}
