<?php
namespace Kenel;
/**
 * ============================================================================
 * 多语言支持
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Lang {
	protected $langs = array();
	/**
	 * 获取一个语言值
	 * @param string $key
	 * @param array $args
	 * @return mixed|multitype:
	 */
  	public function get($key,$args=array()) {
  		if(!isset($this->langs[$key])){
  			trigger_error('Undefined language key of '.$key);
  			return '';
  		}
      return empty($args) ? $this->langs[$key] : vsprintf($this->langs[$key], $args);
  	}
    /**
     * 获取所有语言
     * @return [type] [description]
     */
  	public function getAll(){
  		return $this->langs;
  	}
	/**
	 * 加载语言文件
	 * @param array $lang
	 */
  	public function load(array $lang) {
	  	$this->langs = array_merge($this->langs, $lang);
  	}
}
?>