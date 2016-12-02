<?php
/**
 * ============================================================================
 * 加载类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Load{
	//注册命名空间
	private $namespaces=array();
	//注册类
	private $classes=array();
	//注册目录
	private $dirs=array();
	//类文件后缀
	private $class_ext='.php';
	/**
	 * 设置类文件后缀
	 */
	public function setClassFileExtension($class_ext){
		$this->class_ext=$class_ext;
		return $this;
	}
	/**
	 * 获取类文件后缀
	 * @return string
	 */
	public function getClassFileExtension(){
		return $this->class_ext;
	}
	/**
	 * 注册命名空间
	 * @param array $namespaces
	 */
	public function registerNamespaces(array $namespaces){
		$this->namespaces=array_merge($this->namespaces,$namespaces);
		return $this;
	}
	/**
	 * 获取注册的命名空间
	 * @return array
	 */
	public function getRegisteredNamespaces(){
		return $this->namespaces;
	}
	/**
	 * 注册类
	 * @param array $classes
	 */
	public function registerClasses(array $classes){
		$this->classes=array_merge($this->classes,$classes);
		return $this;
	}
	/**
	 * 获取注册的类
	 * @return array
	 */
	public function getRegisteredClasses(){
		return $this->classes;
	}
	/**
	 * 注册目录
	 * @param array $classes
	 */
	public function registerDirs($dirs){
		if(is_array($dirs)){
			$this->dirs=array_merge($this->dirs,$dirs);
		}else{
			$this->dirs[]=$dirs;
		}
		return $this;
	}
	/**
	 * 获取注册的目录
	 * @return array
	 */
	public function getRegisteredDirs(){
		return $this->dirs;
	}
	/**
	 * 注册自动加载
	 */
	public function register(){
		return spl_autoload_register(array($this,'autoLoad'));
	}
	/**
	 * 注销自动加载
	 */
	public function unregister(){
		return spl_autoload_unregister(array($this,'autoLoad'));
	}
	/**
	 * 自动加载实现
	 * @param string $class_name
	 * @desc
	 * 1、通过命名空间搜索路径
	 * 2、搜索注册的类
	 * 3、搜索注册的目录
	 * @return boolean
	 */
	public function autoLoad($class_name){
		$found_path=false;
		if(isset($this->classes[$class_name])){
			$found_path=ROOT.$this->classes[$class_name].$this->class_ext;
		}
		if(strpos($class_name,'\\')!==false){
			list($namespace,$ext_path)=explode('\\',$class_name,2);
			if(isset($this->namespaces[$namespace])){
				$found_path=ROOT.$this->namespaces[$namespace].'/'.$ext_path.$this->class_ext;
			}else{
				$found_path=ROOT.$class_name.$this->class_ext;
			}
		}else{
			foreach($this->dirs as $v){
				if($this->checkPath(ROOT.$v.'/'.$class_name.$this->class_ext)){
					$found_path=ROOT.$v.'/'.$class_name.$this->class_ext;
					break;
				}
			}
		}
		return $found_path ? $this->includeFile($found_path) : false;
	}
	/**
	 * 查找文件是否存在
	 * @param string $file_path
	 * @return boolean
	 */
	private function checkPath($file_path){
		return file_exists($file_path) && is_file($file_path);
	}
	/**
	 * 只包含一次文件
	 * @param string $file_path
	 * @return boolean
	 */
	public function includeFile($file_path){
		$file_path=str_replace('\\', '/', $file_path);
		static $_include_cache=array();
		$md5_file_path=md5(strtolower($file_path));
		if(isset($_include_cache[$md5_file_path])){
			return true;
		}
		if($this->checkPath($file_path)){
			include $file_path;
			$_include_cache[$md5_file_path]=true;
			return true;
		}
		return false;
	}
}