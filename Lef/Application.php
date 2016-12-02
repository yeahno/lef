<?php
error_reporting(E_ALL);
include __DIR__.'/Lef.php';
use Kenel\Hook;
/**
 * ============================================================================
 * 应用类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Application extends Lef{
	//应用目录
	public $app_dir='App/';
	//默认时区
	public $timezone='PRC';
	//应用运行数据目录
	public $runtime_dir='Runtime/';
	//应用公共目录
	public $public_dir='Public/';
	/**
	 * 应用运行前执行函数
	 * 用于初始化自定义的应用数据及注册服务等
	 */
	protected function onConstruct(){}
	/**
	 * 注册命名空间
	 * @param array $namespaces
	 * @see \Le\Load::registerNamespaces()
	 */
	final public function registerNamespaces(array $namespaces){
		Di::get('load')->registerNamespaces($namespaces);
	}
	/**
	 * 注册类
	 * @param array $classes
	 * @see \Le\Load::registerClasses()
	 */
	final public function registerClasses(array $classes){
		Di::get('load')->registerClasses($classes);
	}
	/**
	 * 注册类目录
	 * @param array $dirs
	 * @see \Le\Load::registerDirs()
	 */
	final public function registerDirs($dirs){
		Di::get('load')->registerDirs($dirs);
	}

	/**
	 * 注册服务
	 * @param string $key
	 * @param string|func|object $value
	 */
	final public function registerService($key,$value){
		Di::set($key,$value);
	}
	/**
	 * 运行入口
	 */
	public function run(){
		date_default_timezone_set($this->timezone);
		define('APP_PATH',ROOT.$this->app_dir);
		define('PUBLIC_PATH',ROOT.$this->public_dir);
		define('RUNTIME',APP_PATH.$this->runtime_dir);
		$this->onConstruct();
		parent::launch();
		$this->onDestroy();
	}
	/**
	 * 应用运行后执行函数
	 * 可用于释放资源等
	 */
	protected function onDestroy(){}
}
?>