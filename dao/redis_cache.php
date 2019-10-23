<?php
include_once dirname(__FILE__) . '../../common/GlobalDefine.php';
class redis_cache
{
    private static $_instance;
    private $_redis = null;

    public function __construct()
    {
        $config       = getConf('ROUTE.REDIS');
        $this->_redis = new Redis();
        $this->_redis->connect($config['HOST'], $config['PORT']);
        $this->_redis->auth($config['PASSWD']);
    }

    private function __clone()
    {

    }

    public function __destruct()
    {
        $this->_redis->close();
    }

    public static function instance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function set($userid, $session)
    {
        return $this->_redis->hMset($userid, $session);
    }

    public function get($userid)
    {
        return $this->_redis->hGetAll($userid);
    }

}
