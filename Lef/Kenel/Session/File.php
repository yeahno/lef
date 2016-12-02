<?php 
namespace Kenel\Session;
/**
 * ============================================================================
 * 基于文件的session类
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class File extends SessionAbstract{
	private $save_path;
	function onStart(){
		ini_set('session.save_handler','user');
		session_set_save_handler(array($this,'open'),array($this,'close'),array($this,'read'),array($this,'write'),array($this,'destroy'),array($this,'gc'));
		session_start();
	}
	//打开SESSION
	function open($save_path, $session_name){
        $this->save_path = $save_path;
        if (!is_dir($this->save_path)) {
            mkdir($this->save_path, 0777,true);
        }
        return true;
	}
	//关闭SESSION
	function close(){
		return true;
	}
	//读取SESSION
	function read($session_id){
		return file_exists($this->save_path.'sess_'.$session_id) ? file_get_contents($this->save_path.'sess_'.$session_id) : null;
	}
	//保存SESSION
	function write($session_id, $sess_data){
		return file_put_contents($this->save_path.'sess_'.$session_id, $sess_data);
	}
	//摧毁SESSION
	function destroy($session_id){
		$file = $this->save_path.'sess_'.$session_id;
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
	}
	//数据库操作方法清理残留过期SESSION
	function gc($maxlifetime){
		foreach (glob($this->save_path.'sess_*') as $file) {
			if (file_exists($file) && filemtime($file) + $maxlifetime < time()) {
				unlink($file);
			}
		}
		return true;
	}
}
?>