define(function(require, exports, module){
	'use strict';

	var tools = require('modules/tools');

	exports.config = {
		process_url : '',
		post_id : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Server error.'
		}
	}
	exports.init = function(){
		tools.ajax_loading_tip('success','test text.',25);
		tools.ready(exports.bind);
	}

	var config = exports.config,
		caches = {};
	exports.bind = function(){
		caches.$btn = document.getElementById('post-fav-' + config.post_id);
		if(!caches.$btn)
			return false;
		ajax();
	}

	function ajax(){
		tools.ajax_loading_tip('loading',config.lang.M00001);
		
		var xhr = new XMLHttpRequest();
		xhr.open('GET',config.process_url + '&post-id=' + caches.$btn.getAttribute('data-post-id'));
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data = JSON.parse(string.trim(request.responseText));}catch(e){}

				if(data && data.status){
					done(data);
				}else{
					fail(request.responseText);
				}
				always(data);
			}
		};
		xhr.onerror = function(){
			tools.ajax_loading_tip('error',config.lang.E00001);
		}
		xhr.send();

		function always(){
			
		}
		function done(data){
			if(data.status === 'success'){
				//tools.ajax_loading_tip(success)
			}else{
				
			}
			tools.ajax_loading_tip(data.status,data.msg);
		}
		function fail(text){
			tools.ajax_loading_tip('error',text);
		}
		
	}
	
});