<?php
namespace Kenel\Cache;
/**
 * ============================================================================
 * 缓存接口类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
interface CacheInterface{
	//获取缓存
	function get($key);
	//设置缓存
	function set($key, $value, $expire = 0);
	//删除缓存
	function delete($key);
	//清空缓存
	function clear();
	//获取缓存实例
	function getInstance();
}
?>