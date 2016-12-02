<style type="text/css">
	#_console-container{
		position:fixed;left:0;bottom:0px;background:#eee;width:100%;height:20px;line-height:25px;font-family:Consolas,"Microsoft YaHei";color:#333;font-size:16px;border-top:1px solid #aaa;
	}
	#_console-container ._status-bar-head{
		float:left;line-height:20px;padding:0 10px;font-size:14px;
	}
	#_console-container ._status-bar-tail{
		float:right;margin-right:30px;font-size:12px;
	}
	#_console-container ._status-bar-tail ._tail-items{
		margin-left:10px;border-left:1px solid #aaa;padding-left:10px;line-height:20px;
	}
	#_console-container ._status-bar-tail ._tail-items:first-child{
		border-left:none;
	}
	#_status-expanse{
		position:fixed;right:0;bottom:0;width:20px;height:12px;padding-top:8px;margin:0 5px;cursor:pointer;z-index:1000;
	}
	#_status-expanse ._status-arrow-up{
	    width:0;
	    height:0;
	    border-left:9px solid transparent;
	    border-right:9px solid transparent;
	    border-bottom:9px solid #888;
	}
	#_status-expanse ._status-arrow-down{
	    width:0;
	    height:0;
	    border-left:9px solid transparent;
	    border-right:9px solid transparent;
	    border-top:9px solid #888;
	}
	#_console-container ._console-items{
		float:left;margin:0;list-style-type: none;height:20px;padding:0;
	}
	#_console-container ._console-items li{
		float:left;border-right:1px solid #aaa;margin:0;padding:0 10px;height:20px;text-align:center;font-size:14px;line-height:20px;cursor:default;
	}
	#_console-container ._console-items li:hover,#_console-container ._console-items li.select{
		background-color:#fff;
	}
	#_console-container ._console-content{
		position:absolute;height:0px;width:100%;bottom:20px;border-top:1px solid #aaa;word-wrap:break-word;word-break:break-all;background:#fff;
	}
	#_console-container ._console-content ._console-content-item{
		width:100%;height:100%;overflow-y:scroll;display:none;
	}
	#_console-container ._console-content ._console-content-item p{
		padding:0 20px;margin:0;
	}
	#_console-container ._console-content ._console-content-item p:hover{
		background:#eee;
	}
	._console-content ._drager-line{
		width:100%;height:2px;cursor:n-resize;background-color:#aaa;display:none;
	}
</style>
<div id="_status-expanse">
	<div class="_status-arrow-down"></div>
</div>
<div id="_console-container">
	<span class="_status-bar-head"><?php echo $head_bar;?></span>
	<ul class="_console-items">
	<?php
		foreach($category as $v){
			if(empty($log[$v])){
				continue;
			}
			echo '<li>'.$v.'</li>';
		}
	?>
	</ul>
	<div class="_status-bar-tail">
		<?php
			foreach($tail_bar as $k=>$v){
				echo '<span class="_tail-items">'.$k.'&nbsp;:&nbsp;<b>'.$v.'</b></span>';
			}
		?>
	</div>
	<div class="_console-content">
		<div class="_drager-line"></div>
		<?php
			foreach($category as $v){
				if(empty($log[$v])){
					continue;
				}
				echo '<div class="_console-content-item">';
				foreach($log[$v] as $val){
					if(is_array($val)){
						$val=print_r($val,true);
						$val=preg_replace("/Array(\s+)\(/m", 'Array(', $val);
						$val=preg_replace("/\n\n/", "\n", $val);
						$val = '<pre>'.$val.'</pre>';
					}
					echo '<p>'.$val.'</p>';
				}
				echo '</div>';
			}
		?>
	</div>
</div>
<script type="text/javascript">
	if(!window.$){
	    var scriptObj= document.createElement("script");
	    scriptObj.type = "text/javascript";
	    scriptObj.src="//cdn.bootcss.com/jquery/1.3.0/jquery.min.js";
	    document.getElementsByTagName('HEAD').item(0).appendChild(scriptObj);
	}
	var i=0;
	console_launch(i);
	function console_launch(i){
		i++;
		if(!window.$){
			if(i>30){
				return;
			}
			setTimeout('console_launch('+i+')',100);
			return;
		}
		$(document).ready(function(){
			$('#_status-expanse').toggle(function(){
				$('#_console-container').slideUp();
				$('#_status-expanse div').removeClass('_status-arrow-down').addClass('_status-arrow-up');
			},function(){
				$('#_console-container').slideDown();
				$('#_status-expanse div').removeClass('_status-arrow-up').addClass('_status-arrow-down');
			});
			$('._console-items li').click(function(){
				if($(this).hasClass('select')){
					$(this).removeClass('select');
					$('._console-content').animate({height:'0px'},'slow',function(){
						$('._console-content ._drager-line').hide();
						$('._console-content ._console-content-item').hide();
					});
				}else{
					var index=$('._console-items li').index(this);
					$(this).addClass('select').siblings().removeClass('select');
					$('._console-content ._console-content-item').eq(index).show().siblings('._console-content-item').hide();
					$('._console-content').animate({height:'250px'});
					$('._console-content ._drager-line').show();
				}
			});
			var dragging = false;
            var startY=0;
            var height=0;
            $('._console-content ._drager-line').mousedown(function(e) {
                dragging = true;
                e = e || window.event
                startY=e.clientY;
                height=$('._console-content').height();
                this.setCapture && this.setCapture();
                return false;
            });
            $(document).mousemove(function(e) {
                if (dragging) {
	            	e = e || window.event;
	            	var offsetY=height+startY-e.clientY;
	                $('._console-content').css({"height":offsetY + "px"});
	                return false;
                }
            });
            $(document).mouseup(function(e) {
                dragging = false;
                $('._console-content ._drager-line')[0].releaseCapture();
                e.cancelBubble = true;
            });

		});
	}
</script>


