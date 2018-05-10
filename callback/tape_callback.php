<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class tape_callback
{
	private $data;
	private $event_type;
	private  $stream_id;
	private $video_id;
	private $video_url;
	private $start_time;
	private $end_time;
	private $dao_live;
	private $check_t;
	private $check_sign;

    private function init()
    {
        $request = file_get_contents("php://input");
        component_log(INFO, EC_OK, "callback report request para: ".$request);
        
        $this->data = json_decode($request, true);
        if(!$this->data)
        {
        	component_log(ERROR, EC_OK, " request para EC_SYSTEM_INVALID_JSON_FORMAT");
        	return EC_SYSTEM_INVALID_JSON_FORMAT;	
        }
        
        if(array_key_exists("t",$this->data)
        		&&array_key_exists("sign",$this->data)
        		&&array_key_exists("event_type",$this->data) 
        		&& array_key_exists("stream_id",$this->data))
        {
        	$check_t = $this->data['t'];
        	$check_sign = $this->data['sign'];
        	$this->event_type = $this->data['event_type'];
        	$this->stream_id = $this->data['stream_id'];
        }
        else
        {
        	component_log(ERROR, EC_OK, " request para EC_SYSTEM_INVALID_PARA");
        	return EC_SYSTEM_INVALID_PARA;
        }
        $md5_sign =  GetCallBackSign($check_t);
        if( !($check_sign == $md5_sign) )
        {
        	component_log(ERROR, EC_OK, " check_sign error:" . $check_sign . ":" . $md5_sign);
        	return EC_SYSTEM_INVALID_SIGN;
        }        
        if($this->event_type == 100)
        {
        	if(array_key_exists("video_id",$this->data) && 
        			array_key_exists("video_url",$this->data) &&
        			array_key_exists("start_time",$this->data) &&
        			array_key_exists("end_time",$this->data) &&
        			array_key_exists("file_format",$this->data) )
        	{
        		$this->video_id = $this->data['video_id'];
        		$this->video_url = $this->data['video_url'];
        		$this->start_time = $this->data['start_time'];
        		$this->end_time = $this->data['end_time'];
        		$this->format_type = $this->data['file_format'];
        	}
        	else
        	{
        		component_log(ERROR, EC_OK, " EC_SYSTEM_INVALID_PARA error");
        		return EC_SYSTEM_INVALID_PARA;
        	}
        }        
        
        $config = getConf('ROUTE.DB');     
        $this->dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        return  0;
    }
    

    public function process()
    {
        component_log(INFO, EC_OK,"callback args=" . var_export($this->data, true));   
        $ret = $this->init();
        if( $ret == 0)
        { 
				$stream_id = $this->stream_id;
		        if ($this->event_type == 100)
		 		{
		 			$duration = $this->end_time - $this->start_time;
		 			if ( $duration > TAPE_DURATION_TIME )
		 			{
		 				$ret = $this->dao_live->addTapeFile($stream_id,
									 					$this->video_id,
									 					$this->video_url,
		 												$this->start_time,
									 					$this->end_time,
		 												$this->format_type);
		 			}
		 			else 
		 			{
		 				$ret = 0;
		 				component_log(ERROR, EC_OK, "tape duration too short:" . strval($duration) ."|" . $stream_id . "|" . $this->video_id);
		 			}
		 			
		 		}
        }
          
        $result = array(
        		'code' => $ret 
        );        
 		$json_result =  json_encode($result); 		
 		header("Content-Length:".strlen($json_result));
 		echo $json_result;
 		
 		component_log(INFO, EC_OK, "process result:" . $ret);
    }
}


//process
$tapecallback = new tape_callback();
$tapecallback->process();
exit(0);

?>