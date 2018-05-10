<?php
require_once dirname(__FILE__) . '/../dao_base/dao.class.php';

class dao_live extends Dao
{
    public function addUGCData($userid, $file_id, $play_url, $title, $frontcover, $location)
    {
        try {
            $nowTime = time();
            $time = date('Y-m-d H:i:s', $nowTime);
            $tmp_data = array(
                'userid' => $userid,
                'file_id' => $file_id,
                'title' => $title,
                'frontcover' => $frontcover,
                'location' => $location,
                'play_url' => $play_url,
                'create_time' => $time
            );
            $this->session_->ReplaceObject(
                'tb_ugc', $tmp_data);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }


    public function getUGCCount(&$live_count, &$error_message)
    {
        $query_sql = "select count(*) as all_count from tb_ugc";
        try {
            $count_result = $this->session_->ExecuteSelectSql($query_sql);
            $live_count = $count_result[0]['all_count'];
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "get getUGCCount count error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }


    public function getTapeCount(&$tape_count, &$error_message)
    {
        $now_time = time();
        $interval = TAPE_FILE_VALID_TIME;
        $query_sql = "select count(*) as all_count from tb_vod where (" . $now_time . " - unix_timestamp(create_time)) < " . $interval;
        try {
            $count_result = $this->session_->ExecuteSelectSql($query_sql);
            $tape_count = $count_result[0]['all_count'];
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "get tape_count count error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function getUGCList($start_row, $row_number, &$live_list, &$error_message)
    {
        //7天内的
        $now_time = time();
        $interval = TAPE_FILE_VALID_TIME;
        $search_sql = "select * from tb_ugc where (" . $now_time . " - unix_timestamp(create_time)) < " . $interval
            . "  order by create_time desc limit " . strval($start_row) . "," . strval($row_number);
        try {
            $result = $this->session_->ExecuteSelectSql($search_sql);

            if (!empty($result)) {
                foreach ($result as $list) {
                    $this->getUserInfo($list['userid'], $userinfo);
                    $one_record = array(
                        'userid' => $list['userid'],
                        'nickname' => $userinfo['nickname'] === null ? "" : $userinfo['nickname'],
                        'avatar' => $userinfo['avatar'] === null ? "" : $userinfo['avatar'],
                        'file_id' => $list['file_id'],
                        'title' => $list['title'],
                        'frontcover' => $list['frontcover'] === null ? "" : $list['frontcover'],
                        'location' => $list['location'] === null ? "" : $list['location'],
                        'play_url' => $list['play_url'],
                        'create_time' => $list['create_time']
                    );
                    array_push($live_list, $one_record);
                }
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "get list error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;

    }

    public function getTapeList($start_row, $row_number, &$live_list, &$error_message)
    {
        //7天内的
        $now_time = time();
        $interval = TAPE_FILE_VALID_TIME;
        $search_sql = " where (" . $now_time . " - unix_timestamp(create_time)) < " . $interval . " AND play_url !=''";

        $query_sql = "select * from tb_vod " . $search_sql . "  order by create_time desc limit " . strval($start_row) . "," . strval($row_number);

        try {
            $result = $this->session_->ExecuteSelectSql($query_sql);

            if (!empty($result)) {
                foreach ($result as $list) {
                    $this->getUserInfo($list['userid'], $userinfo);
                    $one_record = array(
                        'userid' => $list['userid'],
                        'nickname' => $userinfo['nickname'] === null ? "" : $userinfo['nickname'],
                        'avatar' => $userinfo['avatar'] === null ? "" : $userinfo['avatar'],
                        'file_id' => $list['file_id'],
                        'title' => $list['title'],
                        'likecount' => intval($list['like_count']),
                        'viewercount' => intval($list['viewer_count']),
                        'frontcover' => $list['frontcover'] === null ? "" : $list['frontcover'],
                        'location' => $list['location'] === null ? "" : $list['location'],
                        'play_url' => $list['play_url'],
                        'create_time' => $list['create_time'],
                        'hls_play_url' => $list['hls_play_url'],
                        'start_time' => $list['start_time']
                    );
                    array_push($live_list, $one_record);
                }
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "get list error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;

    }

    public function getUserInfo($userid, &$userinfo)
    {
        try {
            $result = $this->session_->GetObject("tb_account", array(
                'userid' => $userid,
            ));
            if (!empty($result)) {
                $userinfo = $result;
            } else {
                return self::ERROR_CODE_DB_NO_RECORD;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "getLiveUser error :" . $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function updateUserInfo($userid, $nickname, $avatar, $sex, $frontcover)
    {
        try {
            $result = $this->session_->GetObject("tb_account", array(
                'userid' => $userid,
            ));
            if (empty($result)) {
                return self::ERROR_CODE_DB_NO_RECORD;
            }

            $ret = $this->session_->UpdateObject("tb_account", array('userid' => $userid), array(
                'nickname' => $nickname,
                'avatar' => $avatar,
                'sex' => $sex,
                'frontcover' => $frontcover,
            ));
            if ($ret != 1) {
                return self::ERROR_CODE_DB_ERROR;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "getLiveUser error :" . $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    private function getRoomInfo($userid, &$roominfo)
    {
        try {
            $result = $this->session_->GetObject("tb_room", array(
                'userid' => $userid,
            ));
            if (!empty($result)) {
                $roominfo = $result;
            } else {
                return self::ERROR_CODE_DB_ERROR;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "getRoomInfo error :" . $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function addRoom($userid, $title, $frontcover, $location)
    {
        try {
            $nowTime = time();
            $time = date('Y-m-d H:i:s', $nowTime);

            $result = $this->session_->GetObject("tb_room", array(
                'userid' => $userid,
            ));

            if (!empty($result)) {
                $this->session_->UpdateObject("tb_room", array('userid' => $userid), array(
                    'userid' => $userid,
                    'title' => $title,
                    'frontcover' => $frontcover,
                    'location' => $location,
                    'create_time' => $time,
                ));
            } else {
                $this->session_->AddObject("tb_room", array(
                    'userid' => $userid,
                    'title' => $title,
                    'frontcover' => $frontcover,
                    'location' => $location,
                    'create_time' => $time,
                ));
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "updateRoom error :" . $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }


    private function getTheSameStreamTape($userid, $start_time, $end_time, &$file_id)
    {
        /*begin:转义字符串，防止sql注入  added by alongchen 2017-01-04 */
        $userid = $this->session_->EscapeString($userid);
        /*end  :转义字符串，防止sql注入  added by alongchen 2017-01-04 */
        try {
            $query_sql = "select * from tb_vod where userid = '" . $userid .
                "' and ((unix_timestamp(start_time) > " . $start_time . " AND unix_timestamp(start_time) < " . $end_time . ") OR " .
                "(unix_timestamp(create_time) > " . $start_time . " AND unix_timestamp(create_time) < " . $end_time . ") OR " .
                "(unix_timestamp(start_time) <= " . $start_time . " AND unix_timestamp(create_time) >= " . $end_time . ") OR " .
                "(unix_timestamp(start_time) > " . $start_time . " AND unix_timestamp(create_time) < " . $end_time . ") )";
            $result = $this->session_->ExecuteSelectSql($query_sql);
            if (!empty($result) && count($result) == 1) {
                $file_id = $result[0]['file_id'];
                return 1;
            }
            return -1;
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "getLiveUser error :" . $userid . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        //	return self::ERROR_CODE_SUCCESSFUL;
    }


    public function addTapeFile($stream_id, $video_id, $video_url, $start_time, $end_time, $format_type)
    {
        try {
            $ids = explode('_', $stream_id, -1);
            $stream_id_after = implode("_", $ids);
            $ids = explode('_', $stream_id_after, 2);
            $userid = $ids[1];

            $ret = $this->getRoomInfo($userid, $roominfo);
            if ($ret != self::ERROR_CODE_SUCCESSFUL) {
                mysql_log(ERROR, EC_OK, "getRoomInfo Error:" . $userid);
                return self::ERROR_CODE_DB_ERROR;
            }


            //先查询db中是否已经有同一个stream_id的一种格式的记录了，如果有，更新。如果没有，新增记录
            $sel_ret = $this->getTheSameStreamTape($roominfo['userid'], $start_time, $end_time, $sel_file_id);
            if (self::ERROR_CODE_DB_ERROR == $sel_ret) {
                mysql_log(ERROR, EC_OK, " getTheSameStreamTape Error:" . $userid);
                return self::ERROR_CODE_DB_ERROR;
            } elseif (1 == $sel_ret) {
                if ($format_type === "mp4") {
                    $tmp_data = array('play_url' => $video_url);
                } else {
                    $tmp_data = array('hls_play_url' => $video_url);
                }
                $this->session_->UpdateObject('tb_vod',
                    array('userid' => $roominfo['userid'], 'file_id' => $sel_file_id),
                    $tmp_data);
            } //老的新增逻辑
            elseif (-1 == $sel_ret) {
                $tmp_data = array(
                    'userid' => $roominfo['userid'],
//	    				'play_url' => $video_url,
                    'file_id' => $video_id,
                    'start_time' => date('Y-m-d H:i:s', $start_time),
                    'create_time' => date('Y-m-d H:i:s', $end_time)
                );
                if ($format_type === "mp4") {
                    $tmp_data['play_url'] = $video_url;
                } else {
                    $tmp_data['hls_play_url'] = $video_url;
                }

                if (isset($roominfo['title'])) {
                    $tmp_data['title'] = $roominfo['title'];
                }
                if (isset($roominfo['frontcover'])) {
                    $tmp_data['frontcover'] = $roominfo['frontcover'];
                }

                if (isset($roominfo['location'])) {
                    $tmp_data['location'] = $roominfo['location'];
                }

                $this->session_->AddObject('tb_vod', $tmp_data);
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "add tape data:" . $stream_id . ":" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function checkAndAddAccountID($userid, $pwdmd5, &$bExist)
    {
        try {
            $result = $this->session_->GetObject("tb_account", array(
                'userid' => $userid,
            ));
            if (empty($result)) {
                $bExist = false;
                $nowTime = time();
                $time = date('Y-m-d H:i:s', $nowTime);
                $tmp_data = array(
                    'userid' => $userid,
                    'password' => $pwdmd5,
                    'create_time' => $time
                );
                $this->session_->AddObject("tb_account", $tmp_data);
            } else {
                $bExist = true;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "checkAndAddAccountID error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function getAccountRecord($userid, &$password)
    {
        try {
            $result = $this->session_->GetObject("tb_account", array(
                'userid' => $userid,
            ));

            if (empty($result)) {
                return self::ERROR_CODE_DB_NO_RECORD;
            } else {
                $password = $result['password'];
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "getAccountRecordByUserID error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function updateAccountInfo($record)
    {
        $query_sql = "UPDATE t_account SET user_sig= '"
            . $record['userSig'] . "', login_time = "
            . $record['loginTime'] . ", last_request_time = "
            . $record['lastRequestTime'] . " WHERE uid= '" . $record['uid'] . "'";
        try {
            $result = $this->session_->ExecuteSelectSql($query_sql);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            mysql_log(ERROR, EC_OK, "updateAccountInfo error :" . $error_message);
            return self::ERROR_CODE_DB_ERROR;
        }
        return self::ERROR_CODE_SUCCESSFUL;
    }

    public function EscapeJson(&$json)
    {
        if (is_object($json)) {
            foreach ($json as $key => &$value)
                $this->EscapeJson($value);
        } else if (is_array($json)) {
            foreach ($json as $key => &$value)
                $this->EscapeJson($value);
        } else if (is_string($json)) {
            $json = $this->session_->EscapeString($json);
        } else {
        }
    }
}

?>
