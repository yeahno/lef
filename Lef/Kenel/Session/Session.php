<?php 
namespace Kenel\Session;
/**
 * ============================================================================
 * 原生的session类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Session extends SessionAbstract{
	function onStart(){
		session_start();
	}
}
?>