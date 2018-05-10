<?php
include_once dirname(__FILE__).'/GlobalDefine.php';


class Ini
{
	private $_confInc;//总的配置文件内容
	private $_confFile;//所有加载的配置文件列表
	private $_conf;//配置内容

	/**
	 * 加载配置文件
	 * 	-- 加载一个配置文件
	 *
	*	@param bool $file				配置文件名	
	 *	@param bool $process_sections		true	返回一个多维数组
	 */
	public function loadFile($file, $process_sections = true)
	{
		$conf = array();
		if ( is_file($file) )
		{
			$conf = parse_ini_file($file, $process_sections);
		}
		return $conf;
	}
	
	/**
	 * 通过统一的inc文件批量加载配置文件
	 * 	-- 加载指定inc文件中的[inc]段中内容(用于指定要加载的配置文件)
	 * 	-- 相同key的配置项，后者会覆盖前者
	 *
	 *	@param bool $file				总体配置文件名	
	 *	@param bool $process_sections		true	返回一个多维数组	 
	 */
	public  function loadInc($file, $process_sections = true)
	{
		$this->_conf = array();
		$this->_confInc = $this->loadFile($file);
		
		$this->_conf['PROJECT'] = $this->_confInc;
		
		if ( isset($this->_confInc['COMMON']) && isset($this->_confInc['COMMON']['INC']) )
			$inc = $this->_confInc['COMMON']['INC'];
		else
			$inc = '';		
		
		$conf = array();
		if ( !empty($this->_confInc) )
		{
			if ( isset($this->_confInc[$inc]) && !empty($this->_confInc[$inc]) )
			{
				foreach ( $this->_confInc[$inc] as $k => $v )
				{
					$pattern = "%(#|;|(//)).*%";
                    $v = preg_replace($pattern,"",$v);
					$v = trim($v);
					$k = trim($k);
					$this->_confFile[$k] = $v;//后者会覆盖前者 
					$this->_conf[$k] = $this->loadFile(ROOT_PATH.'/'.$v, $process_sections);
				}
			}
		}
		return $this->_conf;
	}
		
	/**
	 * 获取配置项
	 * 
	 * @params string $key  key字符串，规则是k1.k2.k3，则表示引用配置$conf中的$conf[inc中的key][section作为key][真正的key]
	 * @return mix    根据配置的具体情况，可能返回字符串，也可能返回数组  
	 */
	public  function get($key)
	{
		$keyArr = explode('.', $key);
		if( false === $keyArr )
		{
			return '';
		}
		else
		{
			$keyStr = '';
			foreach( $keyArr as $k => $v )
			{
				if($k >= 2)
				{
					unset($keyArr[0]);
					unset($keyArr[1]);
					$keyStr .= "['" . implode(".", $keyArr) . "']";
					break;
				}
				$keyStr .= "['".$v."']";
			}
			eval("\$keyStr = \$this->_conf$keyStr ;");
			if ( isset($keyStr) )
			{
				return $keyStr;
			}
		}
		return '';
	}	
	
	/**
	 *	清空配置
	 *		-- 清空已经加载的配置
	 */
	public  function cleanUp()
	{
		$this->_confFile = array();
		$this->_conf = array();
	}
};

//end of script
