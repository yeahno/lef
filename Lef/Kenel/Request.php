<?php
namespace Kenel;
/**
 * ============================================================================
 * 请求类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Request{
	/**
	 * 初始化，初步清理请求数据
	 * @throws \RuntimeException
	 */
	public function __construct(){
		if (isset($_GET['GLOBALS']) ||isset($_POST['GLOBALS']) ||  isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			throw new \RuntimeException('Request tainting',403);
		}
		if (PHP_VERSION<'5.3.0' && get_magic_quotes_gpc()) {
			$_POST = $this->_stripSlashesDeep($_POST);
			$_GET = $this->_stripSlashesDeep($_GET);
			$_COOKIE = $this->_stripSlashesDeep($_COOKIE);
		}
		$_REQUEST=array();
	}
	/**
	 * 过滤自动加的反斜杠
	 * @param array|string $value
	 * @return mixed
	 */
	private function _stripSlashesDeep($value){
		return is_array($value) ? array_map('self::_stripslashesDeep', $value) : stripslashes($value);
	}
	/**
	 * 获取$_GET
	 * @param string $key
	 * @param string $default_value
	 * @param string $data_type
	 * @return mixed
	 */
	public function get($key=null,$default_value=null){
		return $key ? (isset($_GET[$key]) ? $_GET[$key] : $default_value) : $_GET;
	}
	/**
	 * 设置GET
	 * @param string $key
	 * @param string $value
	 */
	public function setGet($key,$value=null){
		if(is_null($value)){
			unset($_GET[$key]);
		}else{
			$_GET[$key]=$value;
		}
	}
	/**
	 * 获取$_POST
	 * @param string $key
	 * @param string $default_value
	 * @param string $data_type
	 * @return mixed
	 */
	public function post($key=null,$default_value=null){
		return $key ? (isset($_POST[$key]) ? $_POST[$key] : $default_value) : $_POST;
	}
	/**
	 * 设置$_POST
	 * @param string $key
	 * @param string $value
	 */
	public function setPost($key,$value=null){
		if(is_null($value)){
			unset($_POST[$key]);
		}else{
			$_POST[$key]=$value;
		}
	}
	/**
	 * 获取$_REQUEST
	 * @param string $key
	 * @param string $default_value
	 * @param string $data_type
	 * @return mixed
	 */
	public function request($key=null,$default_value=null){
		$var=$_GET+$_POST;
		return $key ? (isset($var[$key]) ? $var[$key] : $default_value) : $var;
	}
	/**
	 * 设置$_REQUEST
	 * @param string $key
	 * @param string $value
	 */
	public function setRequest($key,$value=null){
		$this->setGet($key, $value);
		$this->setPost($key, $value);
	}
	/**
	 * 获取COOKIE
	 * @param string $key
	 * @param string $default_value
	 * @return string
	 */
	public function getCookie($key,$default_value=null){
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default_value;
	}
	/**
	 * 获取$_SERVER
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getServer($key=false,$default_value=null){
		return $key ? (isset($_SERVER[$key]) ? $_SERVER[$key] : $default_value) : $_SERVER;
	}
	/**
	 * 获取HTTP协议
	 * @return string
	 */
	public function getScheme(){
		return $this->getServer('REQUEST_SCHEME','http');
	}
	/**
	 * 是否加密请求
	 * @return boolean
	 */
	public function isSecureRequest(){
		return $this->getServer('HTTPS') == 'on';
	}
	/**
	 * 获取请求类型
	 * @return string
	 */
	public function getMethod(){
		return isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '';
	}
	/**
	 * 获取上传文件
	 * @param string $name
	 */
	public function getUploadFile($name=null){
		$files=$_FILES;
		if(empty($files)){
			return $files;
		}
		foreach($files as $k=>$v){
			if(is_array($v['name'])){
				$tmp=array();
				foreach($v['error'] as $key=>$val){
					if(!$val){
						$tmp[$key]=array(
							'name'=>$v['name'][$key],
							'type'=>$v['type'][$key],
							'tmp_name'=>$v['tmp_name'][$key],
							'error'=>$v['error'][$key],
							'size'=>$v['size'][$key]
						);
					}
				}
				$files[$k]=$tmp;
			}else{
				if($v['error']){
					$files[$k]=array();
				}
			}
		}
		return $name ? $files[$name] : $files;
	}
	/**
	 * 是否GET请求
	 * @return boolean
	 */
	public function isGet(){
		return $this->getMethod() == "GET";
	}
	/**
	 * 是否POST请求
	 * @return boolean
	 */
	public function isPost(){
		return $this->getMethod() == "POST";
	}
	/**
	 * 是否AJAX请求
	 * @return boolean
	 */
	public function isAjax(){
		return $this->getServer("HTTP_X_REQUESTED_WITH") == "XMLHttpRequest";
	}
	/**
	 * 是否命令行
	 * @return boolean
	 */
	public function isCli(){
		return php_sapi_name()=='cli';
	}
	/**
	 * 获取主机域名
	 * @return string
	 */
	public function getHost(){
		$scheme = $this->getScheme();
		$host = $this->getServer("HTTP_HOST",$this->getServer("SERVER_NAME"));
		$port = $this->getServer("SERVER_PORT");
		if(!$host){
			return '';
		}
		if(($scheme=='http' && $port==80) || ($scheme=='https' && $port==443)){
			return $scheme.'://'.$host;
		}
		return $scheme.'://'.$host.':'.$port;
	}
	/**
	 * 获取上次请求链接
	 * @return string
	 */
	public function getReferers(){
		$callback=$this->get('callback');
		$callback=$callback ? urldecode($callback) : false;
		return $callback ? $callback : $this->getServer('HTTP_REFERER',false);
	}
	/**
	 * 获取浏览器语言
	 * @return string
	 */
	public function getBestLanguage($default='zh-cn'){
		$lang=$this->getServer('HTTP_ACCEPT_LANGUAGE');
		$langs=preg_split( "/[,;]/",$lang);
		return is_array($langs) ? strtolower(array_shift($langs)) : $default;
	}
	/**
	 * 获取用户IP
	 * @param string $trustForwardedHeader
	 * @return string
	 */
	public function getClientIp(){
		$ip=$this->getServer('HTTP_X_FORWARDED_FOR');
		if ($ip && !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$ip)){
			$ip=false;
		}
		return $ip ? $ip : $this->getServer('REMOTE_ADDR','0.0.0.0');
	}
	/**
	 * 获取网站目录
	 * @return string
	 */
	public function getSiteDir(){
		$script_name=$this->getServer('SCRIPT_NAME');
		return substr($script_name,0,strrpos($script_name,'/'));
	}
}
