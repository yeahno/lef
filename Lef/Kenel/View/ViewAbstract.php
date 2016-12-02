<?php
namespace Kenel\View;
use Di;
/**
 * ============================================================================
 * 模板抽象类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
abstract class ViewAbstract{
	protected $var=array();
	protected $views_dir;
	protected $theme;
	protected $file_ext;
	public function __construct(){
		if(method_exists($this, 'onConstruct')){
			$this->onConstruct();
		}
	}
	/**
	 * 给模板赋值
	 * @param string|array $key
	 * @param string|null $value
	 */
	function assign($key,$value=null){
		if(is_array($key)) {
			$this->var=array_merge($this->var,$key);
		}else{
			$this->var[$key] = $value;
		}
	}
	/**
	 * 设置主题
	 * @param string $theme_name
	 */
	function setTheme($theme_name){
		$this->theme=$theme_name;
	}
	/**
	 * 设置模块后缀
	 * @param string $file_ext
	 */
	function setFileExt($file_ext){
		$this->file_ext=$file_ext;
	}
	/**
	 * 渲染模板
	 * @param string $c_a 控制器/方法
	 */
	abstract function render($c_a='',$datas=array());
	/**
	 * 设置模板目录
	 * @param string $dir_name
	 */
	public function setViewsDir($dir_name){
		if($dir_name[0]==='/' || $dir_name[1]===':'){
			$this->views_dir=$dir_name.'/';
		}else{
			$this->views_dir=APP_PATH;
			if(Di::get('router')->hasModules()){
				$this->views_dir.=Di::get('router')->getModule().'/';
			}
			$this->views_dir.=$dir_name.'/';
			$this->theme && $this->views_dir.=$this->theme.'/';
		}
	}
	/**
	 * 获取模板文件
	 * @param  string $c_a [description]
	 * @return [type]      [description]
	 */
	protected function getViewsFile($c_a=''){
		if(!$this->views_dir){
			$this->setViewsDir('View');
		}
		$c_a=$c_a ?: $this->getControllerActionPath();
		$views_file=$this->views_dir.$c_a.$this->file_ext;
		if(!file_exists($views_file)){
			throw new \Exception('No such view file in '.$views_file,500);
		}
		return $views_file;
	}
	/**
	 * 获取控制器/方法路径
	 * @return string
	 */
	protected function getControllerActionPath(){
		return Di::get('router')->getController().'/'.Di::get('router')->getAction();
	}
}
?>