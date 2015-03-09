define(function(require, exports, module){
	'use strict';
	var $ = require('modules/jquery'),
		jQuery = $,
		tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
		require('theme_custom_user_settings-avatar-cropper');
		
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
		cache.$fm = $('#fm-change-avatar');
		cache.$crop_container = $('#cropper-container');
		cache.$avatar_preview = $('#avatar-preview');
		cache.$crop_done_btn = $('#cropper-done-btn');
		cache.$base64 = $('#avatar-base64');
		if(!cache.$crop_container[0]) return;
		exports.upload();
	}
	exports.upload = function(){
		cache.$file = $('#file');
		
		cache.$file.on({
			drop : file_select,
			change : file_select
		});

		function file_select(e){
			e.stopPropagation();  
			e.preventDefault();  
			cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			cache.file = cache.files[0];
			file_read(cache.file);
			validate();
		}
		function file_read(file){
			var	reader = new FileReader();
			reader.onload = function (e) {
				if(file.type.indexOf('image') === -1){
					alert('Invaild file type.');
					return false;
				}
				cache.$crop_img = $('<img src="' + reader.result + '" alt="cropper">');
				cache.$crop_container.html(cache.$crop_img).fadeIn();
				cache.$avatar_preview.show();
				cache.$crop_img.cropper({
					aspectRatio: 1 / 1,
					preview : '#avatar-preview',
					guides: false,
					minCropBoxWidth : 150,
					minCropBoxHeight : 150
				});
				cache.$crop_done_btn.show();

				
			};
			reader.readAsDataURL(file);	
		}
		function validate(){
			var m = new tools.validate();
				m.process_url = exports.config.process_url + '&' + $.param({
					'theme-nonce' : js_request['theme-nonce']
				});
				m.before = function(){
					cache.base64 = cache.$crop_img.cropper('getDataURL',{
						width : 150,
						height : 150,
					},'image/jpeg',0.8);
					cache.$base64.text(cache.base64);
				}
				m.done = function(data){
					if(data && data.status === 'success'){
						$('.current-avatar > img').attr('src',cache.base64);
						setTimeout(function(){
							location.reload(true);
						},2000);
					}
				};
				m.loading_tx = config.lang.M00001;
				m.error_tx = config.lang.E00001;
				m.$fm = cache.$fm;
				m.init();
		}
		//function 
	}
});