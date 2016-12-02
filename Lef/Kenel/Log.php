<?php
namespace Kenel;
/**
 * ============================================================================
 * 日志记录类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Log{
	//日志目录
	protected $base_path;
	//日志子目录
	protected $sub_dir='';
	//单日志文件大小
	protected $max_file_size;
	//日志格式
	protected $format;
	//是否分模块记录
	protected $is_sep_module;
	//日志记录等级
	protected $permit;
	//日志等级：debug
	const DEBUG = 1;
	//日志等级：info
	const INFO  = 2;
	//日志等级：warning
	const WARNING = 4;
	//日志等级：error
	const ERROR = 8;

	public function __construct(){
		$this->base_path=RUNTIME.'Log/';
		$this->format='[%datetime%] %msg% %n%';
		$this->max_file_size=0;
		$this->permit=self::DEBUG | self::INFO | self::WARNING | self::ERROR;
		$this->sep_file=false;
		$this->log_file_pre_name='';
	}
	/**
	 * 设置日志目录
	 * @param $log_path	目录的绝对路径
	 */
	public function setLogDir($log_path){
		$this->base_path=rtrim($log_path,'/').'/';
		return $this;
	}
	/**
	 * 设置日志子目录
	 * @param $dir_name 子目录名称
	 */
	public function setSubDir($dir_name){
		$this->sub_dir=$dir_name ? rtrim($dir_name,'/').'/' : '';
		return $this;
	}
	/**
	 * 设置最大日志文件大小
	 * @param  [type] $max_file_size [description]
	 * @return [type]                [description]
	 */
	public function setFileSize($max_file_size){
		$this->max_file_size=$max_file_size;
		return $this;
	}
	/**
	 * 设置日志格式
	 * @param  string $format 日志格式
	 * 	  	%datetime%  日期时间
	 *		%msg%		日志信息
	 *		%posfile%   记录日志的文件
	 *		%posline%   记录日志的行数
	 *		%n%         换行符
	 *		%type%		日志类型
	 */
	public function setFormat($format){
		$this->format=$format;
		return $this;
	}
	/**
	 * 允许级别
	 */
	function enable($permit) {
		$this->permit = $this->permit | $permit;
	}
	/**
	 * 禁止级别
	 */
	function disable($permit) {
		$this->permit = $this->permit ^ $permit;
	}
	/**
	 * 不同级别文件是否分开记录
	 */
	public function sepFile($sep=false){
		$this->sep_file=$sep;
	}
	/**
	 * 设置日志文件前缀
	 */
	public function setPreName($pre_name){
		$this->log_file_pre_name=$pre_name;
	}
	/**
	 * 记录debug日志
	 */
	public function debug($msg){
		if ($this->permit & self::DEBUG){
			return $this->record($msg,'debug');
		}
	}
	/**
	 * 记录info日志
	 */
	public function info($msg){
		if ($this->permit & self::INFO){
			return $this->record($msg,'info');
		}
	}
	/**
	 * 记录warning日志
	 */
	public function warning($msg){
		if ($this->permit & self::WARNING){
			return $this->record($msg,'warning');
		}
	}
	/**
	 * 记录error日志
	 */
	public function error($msg){
		if ($this->permit & self::ERROR){
			return $this->record($msg,'error');
		}
	}
	/**
	 * 写入日志
	 * @param  string|array $msg  日志信息
	 * @param  string $type 日志类型
	 * @return bool 是否写入成功
	 */
	public function record($msg,$type){
		$type=strtolower($type);
		\Kenel\Hook::trigger('before_log',array($this,$type,&$msg));
		if(is_array($msg)){
			$new_msg='';
			foreach($msg as $k=>$v){
				if(is_array($v)){
					$v=json_encode($v);
				}
				$new_msg.=' '.$k.'['.$v.']';
			}
			$msg=$new_msg;
		}
		if(!$msg){
			return true;
		}
		list($mictime,$time)=explode(' ',microtime());
		$pos=$this->getCalledPosition();
		$replaces=array(
			'%datetime%'=>date('Y-m-d H:i:s',$time).substr($mictime,1,5),
			'%msg%'=>$msg,
			'%posfile%'=>$pos[0],
			'%posline%'=>$pos[1],
			'%n%'=>PHP_EOL,
			'%type%'=>$type
		);
		$dir=$this->base_path.$this->sub_dir;
		if(!is_dir($dir) && !mkdir($dir,0766,true)){
			throw new \RuntimeException('Failed to create directory '.$dir,500);
		}
		$msg=str_replace(array_keys($replaces),array_values($replaces),$this->format);
		if ($this->sep_file){
			$destination= $dir.$this->log_file_pre_name.date('Ymd').'.'.$type.'.log';
		}else{
			$destination= $dir.$this->log_file_pre_name.date('Ymd').'.log';
		}
		if($this->max_file_size >0 && file_exists($destination) && filesize($destination)>=$this->max_file_size){
			rename($destination,$destination.'.'.date('His'));
		}
		\kenel\hook::trigger('before_log_write',array($this,$type,&$msg));
		return file_put_contents($destination,$msg,FILE_APPEND|LOCK_EX);
	}
	/**
	 * 获取调用的位置
	 * @return array(file_path,line)
	 */
	protected function getCalledPosition(){
        $ret = debug_backtrace();
        $rs=array('','');
        foreach ($ret as $item){
        	if(!isset($item['file'])){
        		continue;
        	}
        	$files=pathinfo($item['file']);
            if(in_array($files['basename'],array('Error.php','Log.php'))){
                continue;
            }
            $rs=array(str_replace(ROOT,'/',str_replace('\\','/',$item['file'])),$item['line']);
            break;
        }
        return $rs;
    }
}
?>