<?php
namespace Kenel;
use Kenel\Hook;
use Di;
/**
 * ============================================================================
 * 路由解析代理
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Router{
	//注册的模块
	private $modules=array();
	//默认模块
	private $default_module='Home';
	//默认控制器
	private $default_controller='Index';
	//默认方法
	private $default_action='index';
	//当前请求的模块
	private $module;
	//当前请求的控制器
	private $controller;
	//当前请求的方法
	private $action;
	//Request 类
	private $request;
	//接收的键值
	private $url_key='_url';
	//路由规则
	private $rules=array();
	/**
	 * 初始化
	 */
	function __construct(Request $request){
		$this->request=$request;//Di::get('request');
	}
	/**
	 * 解析路由
	 * @return [type] [description]
	 */
	public function parse(){
		hook::trigger('before_route',$this);
		$url=$this->getUri();
		if($url){
			if(!$this->parseRewrite($url)){
				$this->parseInfo($url);
			}
		}
		hook::trigger('after_route',$this);
		return $this;
	}
	/**
	 * 添加多个路由规则
	 * @param array $rules
	 * @example
	 * array(
	 * 	array(
	 *		'pattern'=>'/blog/{id}/{page}',
	 *		'defaults'=>array(
	 *			'module'=>'home',
	 *			'controller'=>'blog',
	 *			'action'=>'show',
	 *			'id'=>':1',
	 *			'page'=>':2'
	 *		),
	 *		'requirements'=>array(
	 *			'_method'=>'get',
	 *			'id'=>'{int}',
	 *			'page'=>'\d+'
	 *		)
	 *	)
	 * )
	 */
	public function addRules($rules){
		$this->rules=array_merge($this->rules,$rules);
	}
	/**
	 * 解析rewrite
	 * @param string $url
	 * @throws \Exception
	 * @return boolean
	 */
	private function parseRewrite($url){
		if(empty($this->rules)){
			return false;
		}
		$request_method=$this->request->getMethod();
		foreach($this->rules as $v){
			$searchs=array('{module}','{controller}','{action}','{int}','{id}','{string}');
			$replaces=array('(\w+)','(\w+)','(\w+)','(\d+)','(\d+)','(\w+)');
			if(isset($v['requirements'])){
				if(isset($v['requirements']['_method'])){
					if($request_method!=strtoupper($v['requirements']['_method'])){
						continue;
					}
					unset($v['requirements']['_method']);
				}
				if(!empty($v['requirements'])){
					foreach($v['requirements'] as $key=>$val){
						$searchs[]='{'.$key.'}';
						$replaces[]='('.$val.')';
					}
				}
			}
			$pattern=str_replace($searchs, $replaces, $v['pattern']);
			if(preg_match_all('#^'.$pattern.'#', $url,$matches,PREG_SET_ORDER)){
				$matches=$matches[0];
				if(isset($v['defaults']) && is_array($v['defaults'])){
					foreach($v['defaults'] as $key=>$val){
						$val=$val[0]==':' ? $matches[substr($val,1)] : $val;
						if(in_array($key,array('module','controller','action'))){
							$method='set'.ucfirst($key);
							$this->$method($val);
						}else{
							$this->request->setGet($key,$val);
						}
					}
				}else{
					throw new \InvalidArgumentException('Rewrite Rules of Defaults not is array',500);
				}
				return true;
			}
		}
		return false;
	}
	/**
	 * 使用PATHINFO方式解析
	 * @param string $url
	 */
	private function parseInfo($url){
		$args=explode('/',ltrim($url,'/'));
		if($this->hasModules()){
			$this->setModule(array_shift($args));
		}
		isset($args[0]) && $this->setController(array_shift($args));
		isset($args[0]) && $this->setAction(array_shift($args));
		$parms_count=count($args);
		if($parms_count){
			for($i=0;$i<$parms_count;$i=$i+2){
				$this->request->setGet($args[$i],(isset($args[$i+1]) ? $args[$i+1] : ''));
			}
		}
	}
	/**
	 * 获取URI
	 * @return string
	 */
	private function getUri(){
		$url='';
		if($this->request->isCli()){
			$argvs=$this->request->getServer('argv');
			if(isset($argvs[1])){
				$url='/'.$argvs[1];
			}
		}else{
			$url=$this->request->get($this->url_key);
			if(!$url && isset($_SERVER['PATH_INFO'])){
				$url=$_SERVER['PATH_INFO'];
			}
		}
		return $url;
	}
	/**
	 * 注册模块
	 * @param array $modules
	 */
	public function registerModules(array $modules){
		foreach($modules as $v){
			$this->registerModule($v);
		}
	}
	/**
	 * 注册模块
	 * @param array $modules
	 */
	public function registerModule($module){
		$this->modules[]=Di::snakeToCamel($module);
	}
	/**
	 * 获取注册的模块
	 * @return array
	 */
	public function getRegisterModules(){
		return $this->modules;
	}
	/**
	 * 是否多模块
	 * @return number
	 */
	public function hasModules(){
		return count($this->modules);
	}
	/**
	 * 设置默认模块
	 * @param string $module
	 */
	public function setDefaultModule($module){
		$this->default_module=Di::snakeToCamel($module);
	}
	/**
	 * 返回默认的模块
	 * @return string
	 */
	public function getDefaultModule(){
		return $this->default_module;
	}
	/**
	 * 设置默认控制器
	 * @param string $module
	 */
	public function setDefaultController($controller){
		$this->default_controller=Di::snakeToCamel($controller);
	}
	/**
	 * 返回默认的控制器
	 * @return string
	 */
	public function getDefaultController(){
		return $this->default_controller;
	}
	/**
	 * 设置默认方法
	 * @param string $module
	 */
	public function setDefaultAction($action){
		$this->default_action=Di::snakeToSCamel($action);
	}
	/**
	 * 返回默认的方法
	 * @return string
	 */
	public function getDefaultAction(){
		return $this->default_action;
	}
	/**
	 * 设置当前的模块
	 * @param array $module
	 */
	public function setModule($module){
		$this->module=Di::snakeToCamel($module);
	}
	/**
	 * 获取当前的模块
	 * @return string
	 */
	public function getModule(){
		return $this->hasModules() ? ($this->module ? $this->module : $this->default_module) : false;
	}
	/**
	 * 设置当前的控制器
	 * @param string $controller
	 */
	public function setController($controller){
		$this->controller=Di::snakeToCamel($controller);
	}
	/**
	 * 获取当前的控制器
	 * @return string
	 */
	public function getController(){
		return $this->controller ? $this->controller : $this->default_controller;
	}
	/**
	 * 设置当前请求的方法
	 */
	public function setAction($action){
		$this->action=Di::snakeToSCamel($action);
	}
	/**
	 * 获取当前的方法
	 * @return string
	 */
	public function getAction(){
		return $this->action ? $this->action : $this->default_action;
	}
}
?>