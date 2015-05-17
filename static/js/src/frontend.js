define(function(require, exports, module){
	'use strict';

	require.async(['modules/lazyload','modules/bootstrap-without-jq'],function(_a,_b){});
	
	var tools = require('modules/tools');
	
	
	exports.config = {
		is_home : false
	
	};
	exports.init = function(){
		tools.ready(exports.hide_no_js);
	};

	exports.hide_no_js = function(){
		var A = function(e){
				return document.querySelectorAll(e);
			},
			$no_js = A('.hide-no-js'),
			$on_js = A('.hide-on-js');
		if($no_js[0]){
			Array.prototype.forEach.call($no_js, function(el){
				el.style.display = 'none';
			});
		}
		if($on_js[0]){
			Array.prototype.forEach.call($on_js, function(el){
				el.style.display = 'block';
			});
		}
	};
});