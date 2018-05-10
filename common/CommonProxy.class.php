<?php

class Component_Video_Proxy
{
    private $_url;

    private $_timeout;


    //init the url and component name
    public function __construct($eventId, $config, $url='')
    {


        //$this->_url = $config['URL'];
        if(strlen($url) > 0){
            $this->_url = $url;
        }else{
            $url_array = explode("#",$config['URL']);	// 被调方地址
            $url_array_size = count($url_array);
            $index = rand(0,$url_array_size - 1);
            $this->_url = $url_array_size==1?$url_array[0]:$url_array[$index];
        }

        $this->_timeout = $config['TIMEOUT'];

    }

    /*
     * 调用包组装
     *
     * @param:
     * 			input:
     * 						$interface_name,
     * 						$package,
     * 						$method,
     */
    protected function packReqSend($package, $method='GET')
    {

    	$tmp_url = $this->_url;
        $request['url'] = $this->_url;
   		$request['data'] = $package;

        $request['timeout'] = $parameter['timeout'] ? $parameter['timeout'] : $this->_timeout;
        $request['method']=$method;
        $http_info = array();
        $ret = json_decode($this->send($request, $http_info), true);
        $cost_time = (int)(floatval($http_info["total_time"])*1000);

        return $ret;
    }

    private function send($request, &$http_info)
    {
        $url = $request['url'];
        $data = $request['data'];
        $timeout = $request['timeout'];
        $method = $request['method'];
        $proxy = $request['proxy'];
        $ch = null;

        if ('POST' === strtoupper($method)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } else if ('GET' === strtoupper($method)) {
            if (is_string($data)) {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . $data;
            } else {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($data);
            }
            component_log(DEBUG, EC_OK,"check_status:" .  $real_url);
            $ch = curl_init($real_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        }

        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        //without send http 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $ret = curl_exec($ch);
        $http_info = curl_getinfo($ch);

        $contents = array(
            'httpInfo' => array(
                'send' => $data,
                'url' => $url,
                'ret' => $ret,
                'http' => $http_info,
            )
        );

        //TODO:need to handle network error, using curl_error()
        if (!$ret) {
            component_log(ERROR, EC_OK, "Component_Video_Proxy failed " . json_encode($contents));
        } else {
            component_log(INFO, EC_OK, "Component_Video_Proxy successful " . json_encode($contents));
        }

        curl_close($ch);
        return $ret;
    }

    /*
     * 调用接口函数
     *
     * @param
     * 			$interface_name
     * 			$data,发送的数据
     * 			$ret,返回的数据
     * 			$error_message，错误描述
     * 			$method，请求的方式
     *
     * return
     * 			bool, 返回是否成功
     */
    public function call($data, &$ret, &$error_message, $method='GET')
    {
        try{
            $ret = $this->packReqSend($data,$method);
            //component_log(DEBUG, EC_OK, var_export($ret, true));
            if($ret && ($ret['ret'] == 0 || $ret['ret'] == 20601) ) {
                return true;
            }else {
                $error_message = $ret["returnMsg"];
                component_log(ERROR, $ret['returnValue'], "packReqSend failed |".$error_message);
                return false;
            }
        }
        catch (Exception $e)
        {
            $error_message = $error_message."|".$e->getMessage();
            component_log(ERROR, EC_NETWORK_ERROR, "Component_Video_Proxy failed " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function _dc_report($interfaceName, $ret, $cost_time)
    {
        $ifid =getDCCallCommonProxyInterfaceId($interfaceName);
        $mip = $_SERVER['SERVER_ADDR'].":".$_SERVER["SERVER_PORT"];
        $sip = str_replace('/','',str_replace('http://','',$this->_url));
        $retval = (!$ret)? EC_NEWWORK_ERROR:$ret["returnValue"];

        $dc_result = 0;
        if($retval == 0)
        {
            $dc_result = 0;
        }
        else if($retval == EC_NEWWORK_ERROR)
        {
            $dc_result = 1;
        }
        else
        {
            $dc_result = 2;
        }
        dc_report_modulelog(MODULE_VOD_WEBSERVICE,
            MODULE_COMMON_PROXY,
            $ifid, $mip, $sip,
            $retval, $dc_result, $cost_time);
    }
}
