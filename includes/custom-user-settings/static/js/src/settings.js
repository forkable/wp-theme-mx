define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
	require('modules/jquery.validate');

	exports.config = {
		process_url : '',
		
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Sorry, server error please try again later.'
		}
	}
	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		$(document).ready(function(){
			exports.bind();
		})
	}
	exports.bind = function(){
		cache.$fm = $('.user-form');
		if(!cache.$fm[0]) return false;
		fm_validate(cache.$fm);
	}
	function fm_validate($fm){
		var m = new tools.validate();
			m.process_url = config.process_url + '&' + $.param({
				'theme-nonce' : js_request['theme-nonce']
			});
			m.loading_tx = config.lang.M00001;
			m.error_tx = config.lang.E00001;
			m.$fm = $fm;
			m.init();
	}
});