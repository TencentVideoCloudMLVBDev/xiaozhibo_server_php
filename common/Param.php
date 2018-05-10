<?php
include_once dirname(__FILE__) . '/Common.php';


class Param {
    private $succ = array('result' => true);
    
    public function checkParam(array $rules = array(), &$args) {
        foreach ($rules as $key => $rule) {    	
            $result = $this->checkRule($rule, $args, $key);
            
            if (!$result['result']) { return $result; }
        }
        
        return $this->succ;
    }
    
    private function checkRule($rule, &$args, $key) {
        if (!is_array($rule)) {
            $rule = array('type' => $rule);
        } 
        
        $type = $rule['type'];
        $type || $type = $rule[0];
        
        switch ($type) {
            case "int/array":
                if (isset($args[$key]) && !is_array($args[$key])) {
                    $args[$key] = (array) $args[$key];
                }
                
                $result = $this->checkIntArr($rule, $args, $key);
                break;
            case "string/array":
                if (isset($args[$key]) && !is_array($args[$key])) {
                    $args[$key] = (array) $args[$key];
                }
                
                $result = $this->checkStrArr($rule, $args, $key);
                break;
            default:
                $type || $type = 'default';
                $funcName = "check" . ucfirst($type);
                $result = $this->$funcName($rule, $args, $key);
                break;
        }
        
        return $result;
    }
    
    private function error($msg) {
        return array('result' => false, 'msg' => $msg);
    }
    
    /**
     * 检查nullable
     * @author benzhan
     */
    private function checkBase($rule, &$args, $key) {
        $value = $args[$key];        
        
        //判断是否可空
        if ($rule['nullable'] && $value === null) {
            return $this->succ + array('nullable' => true);
        }
        
        //判断是否在enum中
        if ($rule['enum'] && !in_array($args[$key], $rule['enum'])) {
            return $this->error("{$key}:{$args[$key]} is not in " . var_export($rule['enum'], true));
        }
        
        //判断是否是可为0或空字符串
        //TODO:check logic here!
        if ($rule['emptyable'] && !$value && $value !== null) {
            //return $this->succ + array('nullable' => true);
            return $this->succ;
        }
        //判断是否为空
        if (!$value) {
            return $this->error($key . ' is null or empty!');
        }

        return $this->succ;
    }
    
    private function checkRange($rule, &$args, $key) {
        $range = $rule['range'];
        
        if ($range) {
            $range = trim($range);
            $ranges = explode(',', $range);
            
            $errMsg = $args[$key] . " is not in range {$range}";
          //  $from = (float) trim(substr($ranges[0], 1));
            $from = (string) trim(substr($ranges[0], 1));
            
            if ($from != '-') {
                $flag = $ranges[0][0];
                if ($flag == '[' && $from != '-' && $args[$key] < (float)$from) {
                    return $this->error($errMsg);
                } else if ($flag == '(' && $args[$key] <= $from) {
                    return $this->error($errMsg);
                }
            }

            //$to = (float) trim(substr($ranges[1], 0, -1));
            $to = (string) trim(substr($ranges[1], 0, -1));
            if ($to != '+') {
                $flag = substr($ranges[1], -1);
                if ($flag == ']' && $args[$key] > $to) {
                    return $this->error($errMsg);
                } else if ($flag == ')' && $args[$key] >= $to) {
                    return $this->error($errMsg);
                }
            }

        }

        return $this->succ;
    }
    
    private function checkDefault($rule, &$args, $key) {
        //int类型默认允许为0
        isset($rule['emptyable']) || $rule['emptyable'] = true;
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $result = $this->checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return $this->succ;
    }
        
    private function checkInt($rule, &$args, $key) {
    	
        //int类型默认运行为0
        isset($rule['emptyable']) || $rule['emptyable'] = true;
        
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $copyId = $args[$key];
        $id = (int) $args[$key];
        if (strlen($copyId) != strlen($id)) {
            return $this->error("{$key}:{$args[$key]} is not int!");
        }

        $args[$key] = $id;
        $result = $this->checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return $this->succ;
    }
    
    private function checkIp($rule, &$args, $key) {
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
                
        $ipName = $args[$key];
        $pattern = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
        if (!preg_match($pattern, $ipName)) {
            return $this->error("{$key}:{$ipName} is not valid ip format!");
        }
        
        return $this->succ;
    }
    
    private function checkFloat($rule, &$args, $key) {
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $copyId = $args[$key];
        $id = (float) $args[$key];
        if (strlen($copyId) != strlen($id)) {
            return $this->error("{$key}:{$args[$key]} is not float!");
        }
        
        $args[$key] = $id;
        $result = $this->checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return $this->succ;
    }
    
    private function checkString($rule, &$args, $key) {        
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $args[$key] = (string) $args[$key];
        $args[$key] = trim($args[$key]);
        $maxLen = (int) $rule['maxLen'];
        $minLen = (int) $rule['minLen'];
        if ($maxLen && strlen($args[$key]) > $maxLen) {
            return $this->error($key . '\'s len > ' . $maxLen);
        }
        
        if ($minLen && strlen($args[$key]) < $minLen) {
            return $this->error($key . '\'s len < ' . $minLen);
        }
        
        if ($rule['reg'] && !preg_match('/' . $rule['reg'] . '/', $args[$key])) {
            return $this->error($key . ' preg_match error! The reg rule is:' . $rule['reg']);
        }
        
        return $this->succ;
    }
    
    private function checkArray($rule, &$args, $key) {
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        if (!is_array($args[$key])) {
            return $this->error($key . ' is not array!');
        } 
        
        if ($rule['elem']) {
            foreach ($args[$key] as $i => $value) {
                $result = $this->checkRule($rule['elem'], $args[$key], $i);
                if (!$result['result']) { 
                    $result['msg'] .= " => [parent:{$key}]"; 
                    return $result; 
                }
            } 
        }
        
        return $this->succ;
    }
    
    private function checkIntArr($rule, &$args, $key) {
        return $this->_checkRuleArr($rule, $args, $key, 'int');
    }
    
    private function checkStrArr($rule, &$args, $key) {
        return $this->_checkRuleArr($rule, $args, $key, 'string');
    }
    
    private function checkIpArr($rule, &$args, $key) {
        return $this->_checkRuleArr($rule, $args, $key, 'ip');
    }
    
    private function _checkRuleArr($rule, &$args, $key, $type = 'int') {
        $result = $this->checkArray($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        foreach ($args[$key] as $i => $str) {
            $result = $this->checkRule($type, $args[$key], $i);
            if (!$result['result']) { 
                $result['msg'] .= " => [parent:{$key}]"; 
                return $result; 
            }
        }
                
        return $this->succ;
    }
    
    
    private function checkObject($rule, &$args, $key) {
        $result = $this->checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) { return $result; }
        
        foreach ($rule['items'] as $k => $r) {
            $result = $this->checkRule($r, $args[$key], $k);
            if (!$result['result']) { 
                $result['msg'] .= " => [parent:{$key}]"; 
                return $result; 
            }
        }
                
        return $this->succ;
    }
}


//end of script
