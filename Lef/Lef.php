<?php
use Kenel\Hook;
/**
 * ============================================================================
 * 核心类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Lef{
	function __construct(){
		$this->initDefine();
		$this->registerAutoload();
		$this->registerErrorHandler();
	}
	/**
	 * 定义全局常量
	 */
	final private function initDefine(){
		defined('DEBUG') || define('DEBUG',false);
		DEBUG ? error_reporting(E_ALL) : error_reporting(0);
		define('ROOT',str_replace('\\','/',realpath(dirname(dirname(__FILE__))).'/'));
		define('LEF_VERSION','LeFramework 1.0');
	}
	/**
	 * 注册自动加载
	 */
	final private function registerAutoload(){
		include ROOT.'Lef/Load.php';
		$load=new Load();
		//注册系统默认的命名空间
		$load->registerDirs('Lef')
		->registerNamespaces(array(
			'Kenel'=>'Lef/Kenel',
			'Library'=>'Lef/Library',
			'Util'=>'Lef/Util'
		))->register();
		Di::set('load',$load);
	}
	/**
	 * 注册错误处理
	 */
	final private function registerErrorHandler(){
		$error=Di::get('error');
		set_error_handler(array($error,'catchError'));
		set_exception_handler(array($error,'catchException'));
		register_shutdown_function(array($error,'catchShutdown'));
	}
	/**
	 * 运行框架服务
	 */
	public function launch(){
		Hook::trigger('app_boot',$this);
		$request=Di::get('request');
		$router=Di::get('router')->parse();
		$module=$router->getModule();
		$controller=$router->getController();
		$action=$router->getAction();
		$params=$request->get();
		Hook::trigger('before_dispatch',array($this,&$module,&$controller,&$action,&$params));
		$dispatch=Di::get('dispatch');
		$response=Di::get('response');
		if($dispatch->exec($module,$controller,$action,$params)->isSuccess()){
			$response->setStatus(200)->appendBody($dispatch->getResult())->send();
		}else{
			Di::get('error')->notFound();
		}
		Hook::trigger('after_dispatch',array($this,&$module,&$controller,&$action,&$params));
		Hook::trigger('app_shutdown',$this);
	}
}
?>
