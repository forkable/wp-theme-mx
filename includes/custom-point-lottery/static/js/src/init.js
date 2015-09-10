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
			exports.bind();
		});
	}

	exports.bind = function(){
		cache.$hgihlight_point = I('modify-count');
		cache.$point_count = I('point-count');
		cache.$fm = I('fm-lottery');
		if(!cache.$fm)
			return false;
		exports.submit();
	}
	exports.submit = function(){
		var vld = new tools.validate();
		vld.process_url = config.process_url;
		vld.loading_tx = config.lang.M01;
		vld.error_tx = config.lang.E01;
		vld.$fm = cache.$fm;

		vld.done = done;
		vld.always = always;
		vld.init();
			
		function done(data){
			if(data.status === 'warning'){
				tools.ajax_loading_tip(data.status,data.msg);
			}
			/** set new point */
			highlight_point(parseInt(data['new-points']) - parseInt(cache.$point_count.innerHTML));
		}
		function always(){
			cache.$fm.querySelector('.submit').removeAttribute('disabled');
		}
	}
	function highlight_point(point){
		if(point > 0){
			cache.$hgihlight_point.classList.add('plus');
			cache.$hgihlight_point.innerHTML = '+' + point;
		}else{
			cache.$hgihlight_point.classList.add('mins');
			cache.$hgihlight_point.innerHTML = point;
		}
		cache.$hgihlight_point.style.display = 'inline';
		setTimeout(function(){
			cache.$hgihlight_point.classList.remove('plus');
			cache.$hgihlight_point.classList.remove('mins');
			cache.$hgihlight_point.style.display = 'none';
			cache.$point_count.innerHTML = parseInt(cache.$point_count.innerHTML) + point;
		},2000);
	}
	function I(e){
		return document.getElementById(e);
	}
});