<?php
namespace Kenel;
use Kenel\Hook;
use Di;
/**
 * ============================================================================
 * 错误类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Error{
	/**
	 * 捕获错误信息
	 * @param int $type			错误类型
	 * @param string $message	错误明文
	 * @param string $file		错误文件
	 * @param int $line			错误行数
	 */
	public function catchError($type,$message,$file,$line){
		$type=$this->getErrorType($type);
		Hook::trigger('catch_error',array($type,$message,$file,$line));
		$cont=$this->filter($type.':'.$message.' in '.$file.' on line '.$line);
		Di::get('log')->error($cont);
		if(DEBUG){
			echo $cont.$this->getNl();
		}
	}
	/**
	 * 捕获异常信息
	 * @param \Exception $e
	 */
	public function catchException(\Exception $e){
		//print_r($e->getTraceAsString());
		$msg  =$e->getMessage();
		$file =$e->getFile();
		$line =$e->getLine();
		$trace=array_reverse($e->getTrace());
		$exception_log='';
		foreach($trace as &$v){
			$v['line'] =isset($v['line']) ? $v['line'] : '';
			$v['file'] =isset($v['file']) ? $v['file'] : '';
			$v['class']=isset($v['class'])? $v['class']: '';
			$v['type'] =isset($v['type']) ? $v['type'] : '';
			if(empty($v['args'])){
				$v['args']='';
			}else{
				$tmp='';
				foreach($v['args'] as $key=>$val){
					if(is_object($val)){
						$tmp.='(object) '.get_class($val).',';
					}else if(is_array($val)){
						$tmp.='array,';
					}else{
						$tmp.='\''.htmlspecialchars($val).'\',';
					}
				}
				$v['args']=rtrim($tmp,',');
			}
			$exception_log.=PHP_EOL.'    '.$v['class'].$v['type'].$v['function'].'('.$v['args'].')'.($v['file'] ? ' in '.$v['file'].' on line '.$v['line'] : '');
		}
		Hook::trigger('catch_exception',array($msg,$file,$line,$trace));
		$exception_log= 'Exception:'.$msg.' in '.$file.' on line '.$line.PHP_EOL.'TRACE:'.$exception_log.PHP_EOL;
		Di::get('log')->error($this->filter($exception_log));
		if(DEBUG){
			echo $this->filter('Exception:'.$msg.' in '.$file.' on line '.$line).$this->getNl();
		}
		$this->serverError();

	}
	/**
	 * 捕获致命错误
	 */
	public function catchShutdown(){
		$error=error_get_last();
		if(!empty($error)){
			$this->catchError($error['type'], $error['message'], $error['file'], $error['line']);
			$this->serverError();
		}
		Hook::trigger('catch_shut_down');		
	}
	/**
	 * 获取格式化错误信息
	 * @param string $type
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function getErrorHtml($type,$message,$file,$line){
		$error_string ='<div style="padding:5px 0px;margin:10px 0;background:#FFD;color:#000;border:1px solid #E0E0E0;font-family: \'Microsoft Yahei\', Verdana, arial, sans-serif;">';
		$error_string.='<p style="margin:0px;padding:0px 20px;font-size:14px;font-weight:bold;line-height:150%;"><span style="color:red;">'.$type.'</span>&nbsp;:&nbsp;'.$message.'&nbsp;&nbsp;in <font color="red">'.$file.'</font> on line '.$line.'.</p>';
		$error_string.='<div style="background:#fff;border:1px solid #eee;margin-top:5px;font-family:Courier New,Verdana;font-size:14px;">';
		$filearr=file($file);
		for($i=$line-6;$i<$line+5;$i++){
			if(isset($filearr[$i])){
				$linebg='';
				if(($i+1)==$line){
					$linebg="background:#f00";
				}
				$error_string.='<p style="margin:0px;'.$linebg.'"><span style="border-right:1px solid #eee;display:inline-block;width:30px;margin-right:3px;text-indent:2px;">'.($i+1).'</span>'.str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',nl2br(htmlspecialchars($filearr[$i]))).'</p>';
			}
		}
		$error_string.='</div></div>';
		return $this->filter($error_string);
	}
	/**
	 * 获取格式化异常信息
	 * @param string $msg
	 * @param string $file
	 * @param int $line
	 * @param array $trace
	 */
	public function getExceptionHtml($msg,$file,$line,$trace){
		$exception_string='<div style="padding:5px 0px;margin:10px 0;background:#FFD;color:#000;border:1px solid #E0E0E0;font-family: \'Microsoft Yahei\', Verdana, arial, sans-serif;">';
		$exception_string.='<p style="margin:0px;padding:0px 20px;font-size:14px;font-weight:bold;line-height:150%;"><span style="color:red;">Exception:</span>&nbsp;:&nbsp;'.$msg.'&nbsp;&nbsp;in <font color="red">'.$file.'</font> on line '.$line.'.</p>';
		$exception_string.='<div style="background:#fff;border:1px solid #eee;margin-top:5px;font-family:Courier New,Verdana;font-size:14px;padding:10px;">';
		foreach($trace as $v){
			$exception_string.='<p style="margin:0px;"><b color="black">'.$v['class'].$v['type'].$v['function'].'('.$v['args'].')</b>'.($v['file'] ? ' in '.$v['file'].' on line '.$v['line'] : '').'</p>';
		}
		$exception_string.='</div></div>';
		return $this->filter($exception_string);
	}
	/**
	 * 过滤系统路径
	 * @param string $string
	 * @return string
	 */
	public function filter($string){
		return str_replace(ROOT,'/',str_replace('\\','/',$string));
	}
	/**
	 * 获取错误明文
	 * @param int $error_code
	 * @return string
	 */
	protected function getErrorType($error_code){
		switch($error_code){
			case E_ERROR:
				return 'E_ERROR';
			case E_WARNING:
				return 'E_WARNING';
			case E_PARSE:
				return 'E_PARSE';
			case E_NOTICE:
				return 'E_NOTICE';
			case E_CORE_ERROR:
				return 'E_CORE_ERROR';
			case E_CORE_WARNING:
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR:
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING:
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR:
				return 'E_USER_ERROR';
			case E_USER_WARNING:
				return 'E_USER_WARNING';
			case E_USER_NOTICE:
				return 'E_USER_NOTICE';
			case E_STRICT:
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR:
				return 'E_RECOVERABLE_ERROR';
			case E_ALL:
				return 'E_ALL';
			default:
				return 'UNKNOWN_ERROR';
		}
	}
	/**
	 * 输出404
	 * @return [type] [description]
	 */
	function notFound(){
		$dispatch=Di::get('dispatch');
		$module=Di::get('router')->getModule();
		$content='<h1>404 Not Found</h1>';
		if($dispatch->exec($module,'Error','notFound')->isSuccess()){
			$content=$dispatch->getResult();
		}
		Di::get('response')->setStatus(404)->setBody($content)->send();
	}
	/**
	 * 输出500错误
	 * @return [type] [description]
	 */
	function serverError(){
		$dispatch=Di::get('dispatch');
		$module=Di::get('router')->getModule();
		$content='<h1>500 Internal Server Error</h1>';
		if($dispatch->exec($module,'Error','halt')->isSuccess()){
			$content=$dispatch->getResult();
		}
		Di::get('response')->setStatus(500)->setBody($content)->send();
	}
	/**
	 * 获取换行符
	 */
	protected function getNl(){
		return (Di::get('request')->isAjax() || Di::get('request')->isCli()) ? PHP_EOL : '<br>';
	}
}