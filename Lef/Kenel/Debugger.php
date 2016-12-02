<?php
namespace Kenel;
/**
 * ============================================================================
 * 调试类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Debugger{
	//记录时间
	private $time=array();
	//记录内存
	private $memory=array();
	/**
	 * 输出调试
	 * @param string|array|object $var 输出的内容 
	 * @param string $stop 是否中止运行 
	 */
	static function dump($var,$stop=true){
		if(!is_object($var)){
			$string=print_r($var,true);
			$string=preg_replace("/Array(\s+)\(/m", 'Array(', $string);
			$string=preg_replace("/\n\n/", "\n", $string);
			$string = '<pre>' . htmlspecialchars($string, ENT_QUOTES) . '</pre>';
		}else{
			ob_start();
			var_dump($var);
			$string = ob_get_clean();
			$string = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $string);
			$string = '<pre>' . htmlspecialchars($string, ENT_QUOTES) . '</pre>';
			ob_end_clean();
		}
		//$string=preg_replace("/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $string);
		echo $string;
		$stop && exit();
	}
	/**
	 * 开始计时
	 * @param  string $key
	 */
	public function startTime($key){
		if(!isset($this->time[$key])){
			$this->time[$key]=microtime(true);
		}else{
			trigger_error('time key '.$key.' is existed');
		}
	}
	/**
	 * 获取耗时
	 * @param  [string]  $key     
	 * @param  boolean $formated  是否格式化时间
	 * @param  boolean $forget    是否停止计时
	 * @return [string]           
	 */
	public function getCostTime($key,$formated=true,$forget=true){
		$cost=0;
		if(isset($this->time[$key])){
			$cost=microtime(true)-$this->time[$key];
			$formated && $cost=$this->formatTime($cost);
			$forget && $this->time[$key]=null;
		}else{
			trigger_error('time key '.$key.' is not existed');
		}
		return $cost;
	}
	/**
	 * 开始计算内存消耗
	 * @param  string $key
	 */
	public function startMemory($key){
		if(!isset($this->_memory[$key])){
			$this->memory[$key]=memory_get_usage();
		}else{
			trigger_error('memory key '.$key.' is existed');
		}
	}
	/**
	 * 获取内存消耗
	 * @param string $key
	 * @return number
	 */
	public function getCostMemory($key,$formated=true,$forget=true){
		$cost=0;
		if(isset($this->memory[$key])){
			$cost=memory_get_usage()-$this->memory[$key];
			$formated && $cost=$this->formatSize($cost);
			$forget && $this->memory[$key]=null;
		}else{
			trigger_error('memory key '.$key.' is not existed');
		}
		return $cost;
	}
	/**
	 * 获取内存峰值
	 * @param unknown_type $key
	 */
	public function getPeakMemory($key,$formated=true,$forget=true){
		$cost=0;
		if(isset($this->memory[$key])){
			$cost= memory_get_peak_usage()-$this->memory[$key];
			$formated && $cost= $this->formatSize($cost);
			$forget && $this->memory[$key]=null;
		}else{
			trigger_error('memory key '.$key.' is not existed');
		}
		return $cost;
	}
	/**
	 * 性能分析
	 * @param string|array $func 要分析的函数
	 * @param array $param  要传入函数的参数
	 * @param int  $count   要运行的次数
	 * @return string 耗时
	 */
	public function profiling($func,$param,$count=100000){
		$key='profiling_'.mt_rand(100,999);
		$this->startTime($key);
		for($i=1;$i<=$count;$i++){
			is_array($param) ? call_user_func_array($func,$param) : call_user_func($func,$param);
		}
		return $this->getCostTime($key);
	}
	/**
	 * 格式化字节大小
	 * @param int $size
	 * @param string $format
	 * @param number $round
	 * @return string
	 */
	public function formatSize($size, $format = '%.2f%s', $round = 2){
		$mod = 1024;
		$units = explode(' ','B Kb Mb Gb Tb Pb');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		if (0 === $i) {
			$format = preg_replace('/(%.[\d]+f)/', '%d', $format);
		}
		return sprintf($format, round($size, $round), $units[$i]);
	}
	/**
	 * 格式化时间
	 * @param double $microtime
	 * @param string $format
	 * @param number $round
	 * @return string
	 */
	public function formatTime($microtime, $format = '%.4f%s', $round = 4){
		if ($microtime >= 1) {
			$unit = 's';
			$time = round($microtime,$round);
		} else {
			$unit = 'ms';
			$time = round($microtime*1000);
			$format = preg_replace('/(%.[\d]+f)/', '%d', $format);
		}
		return sprintf($format, $time, $unit);
	}
}
?>