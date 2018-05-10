<?php

class MiniLog {
	private static $_instance;
	private $_path;
	private $_pid;
	private $_handleArr;
	

	private $_eventId;		//请求头部中的字段
	private $_timestamp;	//请求头部中的字段
	
	private $_appid;
	private $_channelId;
	private $_interfaceName;
	
	function __construct($path) {
		$this->_path = $path;
		$this->_pid = getmypid();
		$this->_eventId = rand();
		$this->_timestamp = time();
		$this->_appid = -1;
		$this->_channelId = "";
		$this->_interfaceName = "";
	}
	
	private function __clone() {
		
	}
	
	public static function instance($path = '/tmp/') {
		if(!(self::$_instance instanceof self)) {
			self::$_instance = new self($path);
		}
		
		return self::$_instance;
	}
	
	public function setRequestInfo($eventId, $timestamp, $interfaceName) {
		$this->_eventId = $eventId;
		$this->_timestamp = $timestamp;
		$this->_interfaceName = $interfaceName;
	}
	
	public function setAppIdAndChannelId($app_id, $channel_id) {
		$this->_appid = $app_id;
		$this->_channelId = $channel_id;
	}
	
	private	function getHandle($channelName) {
		if($this->_handleArr[$channelName]) {
			return $this->_handleArr[$channelName];
		}
		date_default_timezone_set('PRC');
		$nowTime = time();
		$logSuffix = date('Ymd', $nowTime);
		$handle = fopen($this->_path . '/' . $channelName . $logSuffix . ".log", 'a');
		$this->_handleArr[$channelName] = $handle;
		return $handle;
	}
	
	public function log($channelName, $message) {
		$handle = $this->getHandle($channelName);
		$nowTime = time();
		$logPreffix = date('Y-m-d H:i:s', $nowTime);
		$keyInfo = "";
		if ($this->_appid >= 0) {
			$keyInfo = "[appid:".$this->_appid."]";
		}
		
		if (!empty($this->_channelId)){
			$keyInfo = $keyInfo."[channelid:".$this->_channelId."]";
		}
		
		fwrite($handle, "[$logPreffix][$this->_pid:$this->_eventId:$this->_timestamp][$this->_interfaceName]".$keyInfo."[$message]\n");
		return true;
	}
	
	function __destruct(){
		foreach ($this->_handleArr as $key => $item) {
			if($item) {
				fclose($item);
			}
		}
	}
}

?>