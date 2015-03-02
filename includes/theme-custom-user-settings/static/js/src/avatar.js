define(function(require, exports, module){
	'use strict';
	var $ = require('modules/jquery'),
		jQuery = $,
		tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
		require('theme_custom_user_settings-avatar-copper');
		
	exports.config = {
		process_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Sorry, server error please try again later.'
		}
	
	};
	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		$(document).ready(function(){
			exports.bind();
		});
	}
	exports.bind = function(){
		cache.$crop_img = $('#cropper-container img');
		if(!cache.$crop_img[0]) return;
		cache.$crop_img.cropper({
			aspectRatio: 1 / 1,
			preview : $('')
		})
	}
});