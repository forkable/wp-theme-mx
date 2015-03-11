define(function(require, exports, module){
	'use strict';

	exports.config = {
		id : ''
	};
	var js_request = require('theme-cache-request'),
		config = exports.config;
		
	exports.init = function(){
		ready(exports.set_views);
	}
	exports.set_views = function(){
		var $views = document.getElementById('post-views');
		if(js_request && js_request['views']){
			if($views)
				$views.innerHTML = parseInt(js_request['views']);
		}
	}
	function ready(fn){
		if (document.readyState != 'loading'){
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}
});