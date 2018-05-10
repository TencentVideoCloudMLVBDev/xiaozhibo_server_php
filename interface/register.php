<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class register extends AbstractInterface
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

    public function process()
    {
        interface_log(INFO, EC_OK,"register args=" . var_export($this->_args, true));
        
        $config = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $dao_live->EscapeJson($this->_args);

        $userid = $this->_args['userid'];
 		//查重
    	$ret = $dao_live->checkAndAddAccountID($userid,$this->_args['password'],$bResult);
    	if($ret != 0)
    	{
    		$this->_retValue = EC_DATABASE_ERROR;
    		return false;
    	}   
        
    	if($bResult == true)
    	{
    		$this->_retValue = EC_USER_EXIST;
    		$this->_retMsg = 'register user id existed';
    		interface_log(INFO, EC_OK, 'Register user id existed: ' . $userid);
    		return false;
    	}
    	
    	//添加用户
    	
        $this->_retValue = EC_OK;
        //$this->_data=array("result"=>EC_OK);
        interface_log(INFO, EC_OK, 'AccountRegister::process() succeed');
        return true;
    }

}

?>
