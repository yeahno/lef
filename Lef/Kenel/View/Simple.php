<?php
namespace Kenel\View;
/**
 * ============================================================================
 * PHP原生模板引擎
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Simple extends ViewAbstract{
	protected $file_ext='.php';
	/**
	 * 渲染
	 * @see ViewInterface::render()
	 */
	public function render($c_a='',$datas=array()){
		$c_a=$c_a ? : $this->getControllerActionPath();
		$views_file=$this->getViewsFile($c_a);
		if(!empty($datas)){
			$this->assign($datas);
		}
		ob_start();
		extract($this->var, EXTR_OVERWRITE);
		include $views_file;
		return ob_get_clean();
	}
}
?>