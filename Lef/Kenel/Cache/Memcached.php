<?php
namespace Kenel\Cache;
/**
 * ============================================================================
 * Memcached类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Memcached implements CacheInterface{
	private $memcached;
	/**
	 * @param array $servers array('127.0.0.1:11211'),可多个
	 * @param string $debug 是否开启调试
	 * @param number $compress_threshold 超过多少字节的数据时进行压缩 
	 * @param string $persistant 是否使用持久连接 
	 */
	public function __construct($servers){
		$this->memcached=new \Memcached();
		if(is_array($servers[0])){
			$this->memcached->addServers($servers);//增加一组服务器
		}else{
			$this->memcached->addServer($v['host'],$v['port'],$v['weight']);
		}
	}
	//增加一个条目，当存在key时，返回false
	public function add($key,$value,$expire=0){
		return $this->memcached->add($key,$value,$expire);
	}
	//获得一个条目，可用数组获得多个
	public function get($key){
		return $this->memcached->get($key);
	}
	//设置一个条目，当存在key时，覆盖
	public function set($key,$value,$expire=0){
		return $this->memcached->set($key,$value,$expire);
	}
	//替换一个条目
	public function replace($key,$value,$expire=0){
		if($this->memcached->get($key)){
			return $this->memcached->replace($key,$value,$expire);
		}else{
			return $this->memcached->set($key,$value,$expire);
		}
	}
	//删除一个条目
	public function delete($key){
		$this->memcached->delete($key);
	}
	//增加一个元素的值
	public function increment($key,$inc_value=1){
		$this->memcached->increment($key,$inc_value);
	}
	//减小元素的值
	public function decrement($key,$dec_value=1){
		$this->memcached->decrement($key,$dec_value);
	}
	//获取服务器统计信息
	public function getStats(){
		return $this->memcached->getStats();
	}
	//获取memcache版本
	public function version(){
		return $this->memcached->getVersion();
	}
	//立即使所有已经存在的元素失效，但并不会真正的释放任何资源，而是仅仅标记所有元素都失效了，因此已经被使用的内存会被新的元素复写
	public function clear(){
		$this->memcached->flush();
	}
	public function close(){
		$this->memcached->close();
	}
	public function getInstance(){
		return $this->memcached;
	}
}
?>