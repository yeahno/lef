<?php
namespace Util\Console;
use Di;
use Kenel\Hook;
class Console{
	protected $panel;
	public function setup(){
		if(!DEBUG){
			return true;
		}
		if(Di::get('request')->isCli() || Di::get('request')->isAjax()){
			return true;
		}
		$this->panel=new Panel();
		$this->registerCategory();
		$this->addHook();
		$this->initSystem();
	}
	protected function registerCategory(){
		$this->panel->registerCategory(array(
			'INFO',
			'ERROR',
			'EXCEPTION',
			'SQL',
			'LOG',
			'SYSTEM'
		));
	}
	protected function addHook(){
		Hook::addListen('app_boot',function(){
			Di::get('debugger')->startTime('_app_');
			Di::get('debugger')->startMemory('_app_');
		});
		Hook::addListen('catch_shut_down',function(){
			$cost_time=Di::get('debugger')->getCostTime('_app_');
			$cost_meme=Di::get('debugger')->getCostMemory('_app_',true,false);
			$peak_meme=Di::get('debugger')->getPeakMemory('_app_',true,false);
			$this->panel->tailBar(array('Time'=>$cost_time,'Memory'=>$cost_meme,'Peak Memory'=>$peak_meme));
			echo Di::get('response')->compress($this->panel->getHtml());
		});
		Hook::addListen('before_sql',function($sql){
			Di::get('debugger')->startTime('sql_'.md5($sql));
		});
		Hook::addListen('after_sql',function($sql,$binds){
			$this->panel->log('['.Di::get('debugger')->getCostTime('sql_'.md5($sql)).']'.$sql.';['.implode(',',$binds).']','SQL');
		});
		Hook::addListen('before_log_write',function($log,$type,$msg){
			$this->panel->log($type.' '.$msg,'LOG');
		});
		Hook::addListen('catch_error',function($type,$message,$file,$line){
			$this->panel->log(Di::get('error')->getErrorHtml($type,$message,$file,$line),'ERROR');
		});
		Hook::addListen('catch_exception',function($msg,$file,$line,$trace){
			$this->panel->log(Di::get('error')->getExceptionHtml($msg,$file,$line,$trace),'EXCEPTION');
		});
		Hook::addListen('before_dispatch',function($le,$module,$controller,$action,$params){
            $this->panel->log('real route: '.$module.'\\'.$controller.'::'.$action,'SYSTEM');
        });
	}
	function log($msg){
		$this->panel->log($msg,'INFO');
	}
	protected function initSystem(){
		$this->panel->log('web root : '.ROOT,'SYSTEM');
		$this->panel->log('os : '.php_uname().'('.Get_Current_User().')','SYSTEM');
		$this->panel->log('server software : '.$_SERVER['SERVER_SOFTWARE'].':'.$_SERVER['SERVER_PORT'],'SYSTEM');
		$this->panel->log('run mode : '.php_sapi_name(),'SYSTEM');
		$this->panel->log('framework : '.LEF_VERSION,'SYSTEM');
	}
}
?>