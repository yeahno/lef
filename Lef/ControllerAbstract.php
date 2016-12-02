<?php
/**
 * ============================================================================
 * 控制器抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
abstract class ControllerAbstract{
	//当前模块名称
	protected $module;
	//当前控制器名称
	protected $controller;
	//当前行为名称
	protected $action;
	/**
	 * 初始化
	 */
	final public function __construct(){
		$this->module=$this->router->getModule();
		$this->controller=$this->router->getController();
		$this->action=$this->router->getAction();
		$this->onConstruct();
	}
	/**
	 * 构造函数
	 */
	public function onConstruct(){
	}
	/**
	 * 获取服务
	 * @param string $key
	 * @return object
	 */
	public function __get($key){
		return Di::get($key);
	}
}
?>