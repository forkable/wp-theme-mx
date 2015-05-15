define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
	
	exports.config = {
		fm_login_id : 'fm-sign-login',
		fm_reg_id : 'fm-sign-register',
		fm_recover_id : 'fm-sign-recover',
		fm_reset_id : 'fm-sign-reset',
		process_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Sorry, server error please try again later.'
		}
	
	};
	exports.cache = {};
	
	exports.init = function(){
		tools.ready(function(){
			exports.sign.init();
			exports.recover.init();
			exports.reset.init();
		});
	};
	/** 
	 * reset
	 */
	exports.reset = {
		init : function(){
			exports.cache.$fm_reset = I(exports.config.fm_reset_id);
			if(!exports.cache.$fm_reset)
				return false;
			tools.auto_focus(exports.cache.$fm_reset);
			var m = new tools.validate();
				m.process_url = exports.config.process_url;
				m.done = function(data){
					if(data && data.status === 'success'){
						setTimeout(function(){
							location.href = location.href;
						},2000);
					}
				};
				m.loading_tx = exports.config.lang.M00001;
				m.error_tx = exports.config.lang.E00001;
				m.$fm = exports.cache.$fm_reset;
				m.init();
		}
	};
	/** 
	 * recover
	 */
	exports.recover = {
		init : function(){
			exports.cache.$fm_recover = I(exports.config.fm_recover_id);
				
			if(!exports.cache.$fm_recover)
				return false;
			
			tools.auto_focus(exports.cache.$fm_recover);
			var m = new tools.validate();
				m.process_url = exports.config.process_url;
				m.loading_tx = exports.config.lang.M00001;
				m.error_tx = exports.config.lang.E00001;
				m.$fm = exports.cache.$fm_recover;
				m.init();
		}
	};
	exports.sign = {
		init : function(){
			exports.cache.$fm_login = I(exports.config.fm_login_id);
			if(exports.cache.$fm_login){
				tools.auto_focus(exports.cache.$fm_login);
				var m = new tools.validate();
					m.process_url = exports.config.process_url;
					m.done = function(data){
						if(data && data.status === 'success'){
							location.href = location.href;
						}
					};
					m.loading_tx = exports.config.lang.M00001;
					m.error_tx = exports.config.lang.E00001;
					m.$fm = exports.cache.$fm_login;
					m.init();
			}else{
				exports.cache.$fm_reg = I(exports.config.fm_reg_id);
				if(exports.cache.$fm_reg){
					tools.auto_focus(exports.cache.$fm_reg);
					var m = new tools.validate();
						m.process_url = exports.config.process_url;
						m.done = function(data){
							if(data && data.status === 'success'){
								location.href = location.href;
							}
						};
						m.loading_tx = exports.config.lang.M00001;
						m.error_tx = exports.config.lang.E00001;
						m.$fm = exports.cache.$fm_reg;
						m.init();
				}
			}
		}
	};
	function I(e){
		return document.getElementById(e);
	}
});