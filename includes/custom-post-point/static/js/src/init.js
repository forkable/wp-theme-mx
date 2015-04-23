define(function(require, exports, module){
	'use strict';

	var tools = require('modules/tools'),
		js_request = require('theme-cache-request');

	exports.config = {
		process_url : '',
		post_id : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Server error.'
		}
	}
	exports.init = function(){
		tools.ready(exports.bind);
	}

	var config = exports.config,
		caches = {};
		
	exports.bind = function(){
		caches.$btns = document.querySelectorAll('.post-point-btn');
		caches.$btn_group = I('post-point-btn-group');
		caches.$ready = I('post-point-loading-ready');
		
		if(!caches.$btns[0])
			return false;
			
		Array.prototype.forEach.call(caches.$btns,function($btn,i){
			$btn.addEventListener('click',ajax, false);
		})
	}

	function ajax(){
		var $btn = this;

		config.post_id = $btn.getAttribute('data-post-id');
		
		tools.ajax_loading_tip('loading',config.lang.M00001);
		caches.$ready.style.display = 'inline-block';
		caches.$btn_group.style.display = 'none';
		
		var xhr = new XMLHttpRequest();
		xhr.open('POST',config.process_url);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded;');
		xhr.send(tools.param({
			'post-id' : $btn.getAttribute('data-post-id'),
			'points' : $btn.getAttribute('data-points'),
			'theme-nonce' : js_request['theme-nonce']
		}));
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
				
				if(data && data.status){
					done(data);
				}else{
					fail(data);
				}
			}else{
				tools.ajax_loading_tip('error',config.lang.E00001);
			}
			always(data);
		};
		xhr.onerror = function(){
			tools.ajax_loading_tip('error',config.lang.E00001);
		}

		function always(){
			caches.$btn_group.style.display = '';
			caches.$ready.style.display = 'none';
		}
		function done(data){
			if(data.status === 'success'){
				tools.ajax_loading_tip(data.status,data.msg,5);
				/** incre points to dom */
				I('post-point-number-' + config.post_id).innerHTML = data.points;
			}else{
				tools.ajax_loading_tip(data.status,data.msg);
			}
		}
		function fail(text){
			tools.ajax_loading_tip('error',config.lang.E00001);
		}
		
	}
	function I(e){
		return document.getElementById(e);
	}
});