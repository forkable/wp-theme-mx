define(function(require, exports, module){
	var tools = require('modules/tools');
	exports.config = {
		process_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Server error or network is disconnected.'
		}
	
	};
	exports.init = function(){
		function ready(fn) {
			if (document.readyState != 'loading'){
				exports.bind();
			} else {
				document.addEventListener('DOMContentLoaded', exports.bind);
			}
		}
	};
	var config = exports.config;
	exports.bind = function(){
		var $btns = document.querySelector('.theme_clean_up-btn');
		if(!$btns)
			return;

		[].forEach.call($btns,function(el,i){
			el.addEventListener('click',action);
		});
	}

	function action(el){
		var $parent = el.parentNode,
			$tip = document.getElementById(el.getAttribute('data-tip-target'));
		/**
		 * tip
		 */
		$parent.style.display = 'none';
		$tip.innerHTML = tools.status_tip('loading',config.lang.M00001);
		$tip.style.display = 'block';
		
		/**
		 * ajax start
		 */
		var xhr = new XMLHttpRequest();
		xhr.open('GET',config.process_url + '&type=' . el.getAttribute('data-action'));
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data = JSON.parse(xhr.responseText);
				if(data && data.status){
					$tip.innerHTML = tools.status_tip(data.status,data.msg);
				}else{
					$tip.innerHTML = tools.status_tip('error',config.lang.E00001);
				}
			}else{
				$tip.innerHTML = tools.status_tip('error',config.lang.E00001);
			}
			$parent.style.display = '';
		};
		xhr.onerror = function(){
			$tip.innerHTML = tools.status_tip('error',config.lang.E00001);
			$parent.style.display = '';
		};
		xhr.send();
	}
});