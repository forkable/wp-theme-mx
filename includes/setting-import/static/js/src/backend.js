define(function(require, exports, module){
	'use strict';

	var tools = require('modules/tools'),
		js_request = require('theme-cache-request');
	
	exports.config = {

		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Error, please try again later.'
		},
		process_url : ''
		
	}
	exports.init = function(){
		tools.ready(function(){
			exports.import();
			exports.export();
		});
	}
	var cache = {},
		config = exports.config,
		I = function(e){
			return document.getElementById(e);
		};
	exports.import = function(){
		cache.$file = I('theme_import_settings-file');
		cache.$tip = I('theme_import_settings-tip');
		if(!cache.$file)
			return false;

		cache.$file.addEventListener('change', select, false);
		cache.$file.addEventListener('drop', select, false);

		function select(e){
			e.stopPropagation();
			e.preventDefault();
			cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			cache.file_count = cache.files.length;
			cache.file = cache.files[0];
			cache.file_index = 0;
			file_upload(cache.files[0]);
		}
		function file_upload(file){
			/**
			 * uploading tip
			 */
			cache.$tip.innerHTML = tools.status_tip('loading',config.lang.M00001);
			cache.$tip.style.display = 'block';
			
			var	reader = new FileReader();
			reader.onload = function (e) {
				var xhr = new XMLHttpRequest(),
					fd = new FormData();
				fd.append('b64',e.target.result);
				fd.append('theme-nonce',js_request['theme-nonce']);
				xhr.open('POST',config.process_url + '&type=import');
				xhr.send(fd);
				xhr.onload = function(){
					if (xhr.status >= 200 && xhr.status < 300) {
						var data;
						try{data = JSON.parse(xhr.responseText)}catch(error){data = xhr.responseText}

						if(data && data.status === 'success'){
							cache.$tip.innerHTML = tools.status_tip(data.status,data.msg);
							/** redirecting */
							setTimeout(function(){
								location.href = location.href;
							},3000);
						}else if(data && data.status === 'error'){
							cache.$tip.innerHTML = tools.status_tip(data.status,data.msg);
						}else{
							cache.$tip.innerHTML = tools.status_tip('error',data);
						}
					}else{
						cache.$tip.innerHTML = tools.status_tip('error',config.lang.E00001);
					}
					cache.$tip.style.display = 'block';
					/** clear the file value */
					cache.$file.value = '';
				};
				xhr.onerror = function(){
					cache.$tip.innerHTML = tools.status_tip('error',config.lang.E00001);
					cache.$tip.style.display = 'block';
					/** clear the file value */
					cache.$file.value = '';
				}
				
			};
			reader.readAsText(file);
		}
	}
	exports.export = function(){
		
	}
});