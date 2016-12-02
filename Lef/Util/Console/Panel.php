<?php
namespace Util\Console;
/**
 * ============================================================================
 * 调试类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		2.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Panel{
	private $log=array();
	private $category=array();
	private $tailBar=array();
	private $headBar='';
	//注册类
	function registerCategory($arr){
		if(is_array($arr)){
			$this->category=array_merge($this->category,$arr);
		}else{
			$this->category[]=$arr;
		}
	}
	/**
	 * 记录要调试的日志
	 * @param unknown $message
	 */
	function log($msg,$category){
		$categroy=strtoupper($category);
		if(in_array($category,$this->category)){
			$this->log[$category][]=$msg;
		}else{
			trigger_error('console category of '.$category.' is not registered');
		}
	}
	/**
	 * 清空记录
	 */
	public function clear($category){
		$categroy=strtoupper($category);
		if(in_array($category,$this->category)){
			$this->log[$category]=array();
		}
	}
	public function tailBar($arr){
		$this->tailBar=$arr;
	}
	public function headBar($str){
		$this->headBar=$str;
	}
	/**
	 * 显示调试记录
	 */
	public function getHtml(){
		$view=new \Kenel\View\Simple();
		$view->setViewsDir(__DIR__);
		return $view->render('view',array('category'=>$this->category,'log'=>$this->log,'tail_bar'=>$this->tailBar,'head_bar'=>$this->headBar));
	}
}
?>