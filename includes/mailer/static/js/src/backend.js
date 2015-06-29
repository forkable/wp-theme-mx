define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools');

	exports.config = {
		process_url : '',
		lang : {
			M01 : 'Loading, please wait...',
			E01 : 'Server error or network is disconnected.'

		}
	}
	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		tools.ready(exports.bind);		
	}

	exports.bind = function(){
		cache.$test_btn = I('theme_mailer-test-btn');
		cache.$test_mail = I('theme_mailer-test-mail');
		cache.$area = I('theme_mailer-area-btn');
		cache.$tip = I('theme_mailer-tip');

		if(!cache.$test_btn || !cache.$test_mail || !cache.$tip || !cache.$area)
			return false;

		cache.$test_btn.addEventListener('click', send_mail, false);
		
	}
	function send_mail(){
		if(cache.$test_mail.value.trim() === ''){
			cache.$test_mail.focus();
			return false;
		}
		tip('loading',config.lang.M01);
		var xhr = new XMLHttpRequest();
		xhr.open('GET',config.process_url + '&test=' + cache.$test_mail.value);
		xhr.send();
		xhr.onload = function(){
			if (xhr.status >= 200 && xhr.status < 400) {
				var data;
				try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
				if(data && data.status){
					tip(data.status,data.msg);
				}
			}else{
				tip('error',xhr.responseText);
			}
			
		};
		xhr.onerror = function(){
			tip('error',config.lang.E01);
		};
	}
	function tip(t,s){
		if(t === 'hide'){
			cache.$area.style.display = 'block';
			cache.$tip.style.display = 'none';
			return false;
		}
		cache.$tip.innerHTML = tools.status_tip(t,s);
		cache.$tip.style.display = 'block';
		if(t === 'loading'){
			cache.$area.style.display = 'none';
		}else{
			cache.$area.style.display = 'block';
		}
	}
	function I(e){
		return document.getElementById(e);
	}
});