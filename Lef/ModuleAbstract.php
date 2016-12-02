<?php
/**
 * ============================================================================
 * 模块抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 * @desc 用于多模块注册服务继承
 */
abstract class ModuleAbstract{
	/**
	 * 初始化
	 */
	final public function __construct(){
		if(method_exists($this,'onConstruct')){
			$this->onConstruct();
		}
	}
	/**
	 * 注册命名空间
	 * @param array $namespaces
	 */
	final public function registerNamespaces(array $namespaces){
		Di::get('load')->registerNamespaces($namespaces);
	}
	/**
	 * 注册类
	 * @param array $classes
	 */
	final public function registerClasses(array $classes){
		Di::get('load')->registerClasses($classes);
	}
	/**
	 * 注册类目录
	 * @param array $dirs
	 */
	final public function registerDirs(array $dirs){
		Di::get('load')->registerDirs($dirs);
	}
	/**
	 * 注册服务
	 * @param string $key
	 * @param string|func|object $val
	 */
	final public function registerService($key,$value){
		Di::set($key,$value);
	}
}
?>