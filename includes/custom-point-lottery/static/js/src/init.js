define(function(require, exports, module){
	'use strict';

	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request')
	exports.config = {
		process_url : '',
		lang : {
			M01 : 'Loading, please wait...',
			E01 : 'Sorry, server is busy now, can not respond your request, please try again later.'
		}
	};
	var cache = {},
		config = exports.config;
	
	exports.init = function(){
		tools.ready(function(){
			exports.select_init();
		});
	}

	exports.bind = function(){
		
	}

	function I(e){
		return document.getElementById(e);
	}
});