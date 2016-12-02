<?php
namespace Kenel\View;
/**
 * ============================================================================
 * 自定义的模板类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Parser extends ViewAbstract{
	private $cache_dir;				//缓存目录
	private $tpl_relates=array();		//文件关联
	private $consts=array();
	protected $file_ext='.html';
	private $literals=array();
	private $blocks=array();
	private $layout=false;

	/**
	 * 初始化
	 */
	public function onConstruct(){
		//ini_set('pcre.backtrack_limit', 1000000);
		$this->cache_dir=RUNTIME.'TplCompiled/';
		if(\Di::get('router')->hasModules()){
			$this->cache_dir.=\Di::get('router')->getModule().'/';
		}
	}
	/**
	 * 添加全局变量
	 * @param array|string $arr
	 * @param string $val
	 */
	public function addConst($arr,$val=''){
		if(is_array($arr)){
			$this->consts=array_merge($this->consts,$arr);
		}else{
			$this->consts[$arr]=$val;
		}
	}
	/**
	 * 渲染模板
	 * @see \kenel\view\viewAbstract::render()
	 */
	public function render($c_a='',$datas=array()){
		$c_a=$c_a ? : $this->getControllerActionPath();
		$cached_file=$this->getCompiledFile($c_a);
		if(!empty($datas)){
			$this->assign($datas);
		}
		ob_start();
		extract($this->var, EXTR_OVERWRITE);
		include $cached_file;
		return ob_get_clean();
	}
	/**
	 * 获取编译后的模板
	 * @throws \RuntimeException
	 */
	private function getCompiledFile($c_a){
		$relates_file=$this->cache_dir.'_relates/'.str_replace('/','_',$c_a).'.php';
		$cached_file = $this->cache_dir.$c_a.'.php';
		$views_file=$this->getViewsFile($c_a);
		$this->tpl_relates[]=$views_file;
		if (!$this->checkCache($cached_file,$relates_file)) {
			$text = file_get_contents($views_file);
			$text = $this->compile($text);
			$this->write($cached_file,$text);
			$this->write($relates_file,'<?php return '.var_export($this->tpl_relates,true).'; ?>');
		}
		return $cached_file;
	}
	/**
	 * 写入文件
	 * @param string $file
	 * @param string $content
	 */
	private function write($file,$content){
		if(!is_dir(dirname($file))){
			mkdir(dirname($file),0766,true);
		}
		if(file_put_contents($file,$content)===false){
			throw new \RuntimeException('Failed to write file '.$file,500);
		}
	}
	/**
	 * 查看编译文件是否过期
	 * @param string $cached_file
	 * @param string $relates_file
	 * @return boolean
	 */
	private function checkCache($cached_file,$relates_file){
		if(DEBUG){
			return false;
		}
		if(!file_exists($cached_file)){
			return false;
		}
		if(!file_exists($relates_file)){
			return false;
		}
		$relates=include_once $relates_file;
		$cache_file_modify_time=filemtime($cached_file);
		foreach($relates as $v){
			if(!is_file($v) || $cache_file_modify_time<filemtime($v)){
				return false;
			}
		}
		return true;
	}
	/**
	 * 解析模板
	 * @param string $text
	 * @return string
	 */
	private function compile($text,$replace=true){
		$text=$this->parseComments($text);
		$text=preg_replace_callback('/\{%\s*literal\s*%\}(.*)\{%\s*endliteral\s*%\}/ims',array($this,'parseLiteral'),$text);
		$text=preg_replace_callback('/\{%\s*(extend|if|else|elseif|endif|foreach|endforeach|include|exec|set)\s*(.*?)\s*%\}/ims',array($this,'parseTags'),$text);
		$text=preg_replace_callback('/\{%\s*(block)\s+(.*?)\s*%\}(.*?)\{%\s*end\1\s*%\}/ims',array($this,'parseTags'),$text);
		$text=preg_replace_callback('/\{\{\s*\$(.*?)\s*\}\}/ms',array($this,'parseVariables'),$text);
		$text=$this->replaceConst($text);
		if($replace){
			$text=$this->replaceBlock($text);
			!DEBUG && $text=$this->clearText($text);
		}
		return trim($text);
	}
	/**
	 * 替换定义的常量
	 * @param string $text
	 * @return string
	 */
	private function replaceConst($text){
		return str_replace(array_keys($this->consts),array_values($this->consts),$text);
	}
	/**
	 * 替换区块
	 * @param string $text
	 * @return string
	 */
	private function replaceBlock($text){
		$text=$this->layout?:$text;
		$searches=array();
		$replaces=array();
		foreach($this->literals as $k=>$v){
			$searches[]='__literal_'.$k.'__';
			$replaces[]=$v;
		}
		foreach($this->blocks as $k=>$v){
			$searches[]='__block_'.$k.'__';
			$replaces[]=$v;
		}
		return str_replace($searches,$replaces,$text);
	}
	/**
	 * 删除注释
	 * @param string $text
	 * @return string
	 */
	private function parseComments($text){
		return preg_replace('/\{#.*?#\}/s', '', $text);
	}
	/**
	 * 解析原样输出
	 * @param array $matches
	 * @return string
	 */
	private function parseLiteral($matches){
		$index=count($this->literals);
		$this->literals[$index]=$matches[1];
		return '__literal_'.$index.'__';
	}
	/**
	 * 清除空字符、垃圾数据
	 * @param string $text
	 * @return mixed
	 */
	private function clearText($text){
		return preg_replace(array("/>\s+</","/>\s+/","/\s+</","/<!--[^!]*-->/s"),array('><','>','<',''), $text);
	}
	/**
	 * 解析标签
	 * @param string $text
	 * @return string
	 */
	private function parseTags($matches){
		$method='tags'.ucfirst($matches[1]);
		if(!method_exists($this,$method)){
			return $matches[0];
		}
		return $this->$method($matches);
	}
	/**
	 * 解析变量
	 * @param array $matches
	 * @return string
	 */
	private function parseVariables($matches){
		$text=trim($matches[1]);
		if(!$text){
			return $matches[0];
		}
		$funcs = explode('|',$text);//带函数
		$var=array_shift($funcs);
		$var='$'.$var;
		!empty($funcs) && $var = $this->parseVarFunction($var,$funcs);
		return '<?php echo '.$var.';?>';
	}
	/**
	 * 分析函数
	 * @param string $name
	 * @param array $vars
	 * @return string
	 */
	private function parseVarFunction($var,$funcs){
		$deny_funcs = array('echo','exit','eval');
		foreach($funcs as $v){
			$args = explode(':',$v,2);
			$args[0]=trim($args[0]);
			switch(strtolower($args[0])) {
				case 'default':
					$var   = 'isset('.$var.')?'.$var.':'.$args[1];
					break;
				default:
					if(!in_array($args[0],$deny_funcs)){
						if(isset($args[1])){
							$args[1] = str_replace('###',$var,$args[1]);
							$var = $args[0].'('.$args[1].')';
						}else if(!empty($args[0])){
							$var = $args[0].'('.$var.')';
						}
					}
			}
		}
		return $var;
	}
	/**
	 * 解析继承标签
	 * @desc 不支持多继承，继承后会以继承的方件为模板，子文件只能以BLOCK区块替换父文件内容
	 * @param array $matches
	 */
	private function tagsExtend($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		$extend_file=$this->getViewsFile($matches[2]);
		$this->tpl_relates[]=$extend_file;
		$layout=$this->compile(file_get_contents($extend_file),false);
		$this->layout=$this->layout ? : $layout;
		return '';
	}
	/**
	 * 解析if
	 * @param array $matches
	 * @return string
	 */
	private function tagsIf($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		return '<?php if('.$matches[2].'){ ?>';
	}
	/**
	 * 解析else
	 * @param array $matches
	 * @return string
	 */
	private function tagsElse($matches){
		return '<?php } else { ?>';
	}
	/**
	 * 解析elseif
	 * @param array $matches
	 * @return string
	 */
	private function tagsElseif($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		return '<?php } else if ('.$matches[2].'){ ?>';
	}
	/**
	 * 解析endif
	 * @param array $matches
	 * @return string
	 */
	private function tagsEndif($matches){
		return '<?php }?>';
	}

	/**
	 * 解析include标签
	 * @desc 将其他文件的内容编译到文件中
	 * @param array $matches
	 * @return string
	 */
	private function tagsInclude($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		$include_file=$this->getViewsFile($matches[2]);
		$this->tpl_relates[]=$include_file;
		return $this->compile(file_get_contents($include_file),false);
	}
	/**
	 * 解析exce标签
	 * @desc 执行控制器里的方法，并输出结果
	 * @param array $matches
	 * @return string
	 */
	private function tagsExec($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		return '<?php echo $this->exec(\''.$matches[2].'\');?>';
	}
	/**
	 * 包含块的具体实现
	 * @param string $var
	 */
	private function exec($var){
		$mca=explode('/',$var);
		$arg_nums=count($mca);
		if($arg_nums<3){
			if($arg_nums==1){
				array_unshift($mca,\Di::get('router')->getController());
			}
			if(\Di::get('router')->hasModules()){
				array_unshift($mca,\Di::get('router')->getModule());
			}else{
				array_unshift($mca,'');
			}
		}
		return \Di::get('dispatch')->exec($mca[0],$mca[1],$mca[2])->getResult();
	}
	/**
	 * 设置变量
	 * @param array $matches
	 * @return string
	 */
	private function tagsSet($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		preg_match_all('/\$(\w*)\s*([\+\-\*%\.])?=\s*(\d+|true|false|\'.*?\'|".*?"|\$\S*)/',$matches[2],$pear,PREG_SET_ORDER);
		if(empty($pear)){
			return $matches[0];
		}
		$php_str='';
		foreach($pear as $v){
			$php_str.='$'.$v[1].$v[2].'='.$v[3].';';
		}
		return '<?php '.$php_str.' ?>';
	}
	/**
	 * 解析区块
	 * @param array $matches
	 * @return string
	 */
	private function tagsBlock($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		$return='';
		if(!isset($this->blocks[$matches[2]])){
			$return='__block_'.$matches[2].'__';
		}
		$this->blocks[$matches[2]]=$this->compile($matches[3],false);
		return $return;
	}
	/**
	 * 解析循环
	 * @param array $matches
	 * foreach name=xx key=k item=v start=n 
	 * @return string
	 */
	private function tagsForeach($matches){
		if(!$matches[2]){
			return $matches[0];
		}
		preg_match_all('/(\w+)\s*=\s*(\S+)/',$matches[2],$ma,PREG_SET_ORDER);
		$attr=array();
		foreach($ma as $v){
			$attr[$v[1]]=$v[2];
		}
		$str='<?php if(isset('.$attr['from'].') && is_array('.$attr['from'].')){';
		if(isset($attr['name'])){
			$str.='$'.$attr['name'].'[\'index\']=0;';
			$str.='$'.$attr['name'].'[\'first\']=1;';
			$str.='$'.$attr['name'].'[\'last\']=count('.$attr['from'].')-1;';
		}
		if(!isset($attr['key'])){
			$attr['key']='k';
		}
		if(!isset($attr['item'])){
			$attr['item']='v';
		}
		$str.='foreach ('.$attr['from'].' as $'.$attr['key'].'=>$'.$attr['item'].'){';
		if(isset($attr['name'])){
			$str.='$'.$attr['name'].'[\'index\']++;';
			if(isset($attr['start'])){
				$str.='if($'.$attr['name'].'[\'index\'] < '.$attr['start'].')continue;';
			}
		}
		$str.='?>';
		return $str;
	}
	/**
	 * 完成循环
	 * @param array $matches
	 * @return string
	 */
	private function tagsEndforeach($matches){
		return '<?php }} ?>';
	}
}