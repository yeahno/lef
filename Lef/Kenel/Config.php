<?php 
namespace Kenel;
/**
 * ============================================================================
 * 配置类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Config{
	//配置
	protected $configs=array();
	/**
	 * 获取配置文件值
	 * @param string $key			配置键，支持a.b获取config['a']['b']
	 * @param string $default_value 默认返回值
	 * @return mixed
	 */
	public function get($key,$default_value=null,$args=array()) {
		$rs=$default_value;
		if(strpos($key,'.')===false){
			$rs=isset($this->configs[$key]) ? $this->configs[$key] : $default_value;
		}else{
			$key_arr=explode('.',$key);
			$cp_configs=&$this->configs;
			foreach($key_arr as $v){
				if(!isset($cp_configs[$v])){
					$rs=$default_value;
				}else{
					$cp_configs=&$cp_configs[$v];
				}
			}
			$rs=$cp_configs;
		}
		return empty($args) ? $rs : vsprintf($rs,$args);
	}
	/**
	 * 返回所有的配置
	 * @return array
	 */
	public function getAll(){
		return $this->configs;
	}
	/**
	 * 设置一个配置值
	 * @param string $key	设置的配置键，支持a.b设置config['a']['b']
	 * @param unknown $value设置的配置值
	 */
	public function set($key, $value) {
		if(strpos($key,'.')===false){
			$this->configs[$key] = $value;
		}else{
			$key_arr=explode('.',$key);
			$cp_configs=&$this->configs;
			foreach($key_arr as $v){
				if(!isset($cp_configs[$v])){
					$cp_configs[$v]=array();
				}else{
					$cp_configs=&$cp_configs[$v];
				}
			}
			$cp_configs=$value;
		}
	}
	/**
	 * 加载一个配置数组
	 * @param array $config_arr 配置数组
	 * @return boolean
	 */
	public function load(array $config_arr) {
		$this->configs = array_merge($this->configs, $config_arr);
	}
}
?>