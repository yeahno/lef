<?php
namespace Kenel;
use Kenel\Hook;
use Di;
/**
 * ============================================================================
 * 响应类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		2.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class response{
	//头信息
	protected $headers=array();
	//待输出的内容
	protected $body='';
	//压缩等级
	protected $compress_level=0;
	protected $status=200;
	/**
	 * 初始化
	 */
	public function __construct(){
		$this->setHeader('Content-Type','text/html;Charset=utf-8');
	}
	/**
	 * 设置头信息
	 * @param string $name
	 * @param string $value
	 */
	public function setHeader($name,$value=''){
		$this->headers[$name]=$value;
		return $this;
	}
	/**
	 * 获取头信息
	 * @param string $name
	 * @return string
	 */
	public function getHeader($name){
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}
	/**
	 * 获取所有头信息
	 * @return array
	 */
	public function getAllHeaders(){
		return $this->headers;
	}
	/**
	 * 设置http响应状态码
	 * @param  int $code [description]
	 * @return [type]       [description]
	 */
	public function setStatus($code) {
        $this->status = $code;
        return $this;
    }
    public function getStatus(){
    	return $this->status;
    }
	/**
	 * 设置响应内容
	 * @param string $body
	 */
	public function setBody($body){
		$this->body=$body;
		return $this;
	}
	/**
	 * 获取响应内容
	 * @return string
	 */
	public function getBody(){
		return $this->body;
	}
	/**
	 * 追加响应内空
	 * @param string $body
	 */
	public function appendBody($body){
		$body && $this->body.=$body;
		return $this;
	}
	/**
	 * 在响应内容前添加响应内容
	 * @param unknown $body
	 */
	public function prependBody($body){
		$body && $this->body=$body.$this->body;
		return $this;
	}
	/**
	 * 设置COOKIE
	 * @param string $name
	 * @param string $value
	 * @param number $expire
	 * @param string $path
	 * @param string $domain
	 */
	public function setCookie($name,$value,$expire=0,$path='/',$domain=''){
		$expire=$expire>0  ? time()+$expire : 0;
		setcookie($name,$value,$expire,$path,$domain);
		return $this;
	}
	protected function sendHeader(){
		http_response_code($this->status);
		$this->setHeader('X-Powered-By',LEF_VERSION);
		foreach($this->headers as $k=>$v){
			if($v){
				header($k.': '.$v);
			}else{
				header($k);
			}
		}
	}
	/**
	 * 输出
	 */
	public function send(){
		$this->body=ob_get_clean().$this->body;
		hook::trigger('before_response',$this);
		if(!headers_sent()){
			$this->body=$this->compress($this->body);
			$this->sendHeader();
		}
		echo $this->body;
		hook::trigger('after_response',$this);
	}
	/**
	 * 设置压缩级别
	 * @param int $level
	 * @throws \Exception
	 * @notice 如果开启了压缩，那么响应后的数据输出有可能不显示
	 */
	public function setCompressLevel($level){
		if($level<0 || $level>9){
			throw new \InvalidArgumentException('Compress level must between 0 and 9',500);
		}
		$this->compress_level=$level;
	}
	/**
	 * 压缩数据
	 * @param string $data
	 * @return string
	 */
	public function compress($body){
		if(!$this->compress_level || !$body){
			return $body;
		}
		$encoding='';
		$http_accpt_encoding=Di::get('request')->getServer('HTTP_ACCEPT_ENCODING');
		if (strpos($http_accpt_encoding, 'gzip') !== false) {
			$encoding = 'gzip';
		}
		if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
			$encoding = 'x-gzip';
		}
		if (!$encoding) {
			return $body;
		}
		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $body;
		}
		if (connection_status()) {
			return $body;
		}
		$body = gzencode($body,$this->compress_level);
		$this->setHeader('Content-Encoding',$encoding);
		$this->setHeader('Vary','Accept-Encoding');
		$this->setHeader('Content-Length',strlen($body));
		return $body;
	}
	/**
	 * 设置缓存
	 * @param  int $expires 到期的时间戳
	 */
	public function cache($expires=false) {
        if ($expires === false) {
            $this->setHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
            $this->setHeader('Cache-Control','no-store, no-cache, must-revalidate');
            $this->setHeader('Cache-Control','post-check=0, pre-check=0');
            $this->setHeader('Cache-Control','max-age=0');
            $this->setHeader('Pragma','no-cache');
        }else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->setHeader('Expires',gmdate('D, d M Y H:i:s', $expires) . ' GMT');
            $this->setHeader('Cache-Control','max-age='.($expires - time()));
        }
        return $this;
    }
	/**
	 * 跳转
	 * @param string $url
	 */
	public function redirect($url,$status=302){
		if(strpos($url,':')===false){
			$url=Di::get('request')->getSiteDir().'/'.$url;
		}
		$this->setStatus($status)->setHeader('Location', $url)->setBody('')->send();
		exit();
	}
}
