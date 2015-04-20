define(function(require, exports, module){
	'use strict';
	var dialog = require('modules/jquery.dialog'),
		js_request 	= require('theme-cache-request');
	require('modules/jquery.validate');
	require('modules/jquery.validate.lang.{locale}');
		
	exports.config = {
		btn_login_id : '.btn-q-login',
		btn_register_id : '.btn-q-register',
		fm_login_id : '#fm-login',
		fm_register_id : '#fm-register',
		process_url : '',
		recover_pwd_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			M00002 : 'Login',
			M00003 : 'Register',
			E00001 : 'Sorry, server error please try again later.'
		}
	
	};
	exports.cache = {};
	
	exports.init = function(){
		jQuery(document).ready(function(){
			if(!js_request['user']['logged']){
				exports.login.bind();
				// exports.register.bind();
			}else{
				exports.logged.init();
			}

		});
	};
	exports.tab = {
		init : function(current_tab){
			require.async(['modules/jquery.kandytabs'],function(_a){
				exports.cache.$tab_container = jQuery(exports.common.tpl());
				exports.cache.$tab_loading = exports.cache.$tab_container.find('#q-sign-loading');
				exports.cache.$tab = exports.cache.$tab_container.find('#q-sign');
				// exports.cache.$fm_login = exports.cache.$tab.find('#q-login');
				// exports.cache.$login_tip = exports.cache.$fm_login.find('.page-tip');
				// exports.cache.$fm_register = exports.cache.$tab.find('#q-register');
				// exports.cache.$register_tip = exports.cache.$fm_register.find('.page-tip');
				// console.log(exports.cache.$tab);
				exports.cache.$tab.KandyTabs({
					trigger : 'click',
					current : current_tab,
					done : function($btn,$cont,$tab,$this){
						exports.tab.done($btn,$cont,$tab,$this);
					},
					custom : function($btn,$cont,index,$tab,$this){
						tools.auto_focus($cont.eq(index)[0]);
					}
				});
			});
		},
		done : function($btn,$cont,$tab,$this){
			$tab.show();
			exports.cache.$tab_loading.hide();
			/** 
			 * dialog
			 */
			exports.common.dialog({
				id : 'q-sign',
				title : exports.config.lang.M00008,
				content : exports.cache.$tab
			});
			exports.cache.$fm_login = exports.cache.$tab.find('#q-login');
			exports.cache.$fm_register = exports.cache.$tab.find('#q-register');
			/** 
			 * validate
			 */
			var login_validate = new tools.validate();
				login_validate.process_url = exports.config.process_url + '&' + jQuery.param({
					'theme-nonce' : js_request['theme-nonce']
				});
				login_validate.loading_tx = exports.config.lang.M00001;
				login_validate.error_tx = exports.config.lang.E00001;
				login_validate.$fm = exports.cache.$fm_login;
				login_validate.done = function(data){
					if(data && data.status === 'success'){
						location.href = location.href;
					}
				};
				login_validate.init();
			var register_validate = new tools.validate();
				register_validate.process_url = exports.config.process_url + '&' + jQuery.param({
					'theme-nonce' : js_request['theme-nonce']
				});
				register_validate.loading_tx = exports.config.lang.M00001;
				register_validate.error_tx = exports.config.lang.E00001;
				register_validate.$fm = exports.cache.$fm_register;
				register_validate.done = function(data){
					if(data && data.status === 'success'){
						location.href = location.href;
					}else if(data && data.status === 'error'){
						if(data.id){
							switch(data.id){
								case 'empty_user_login':
								case 'invalid_nickname':
									jQuery('#r-user-name').select();
								break;
								case 'email_exists':
									jQuery('#r-user-email').select();
								break;
							}
						}
					}
				};
				register_validate.rules = {
					'user[pwd-again]' : {
						equalTo : '#r-user-pwd'
					}
				};
				register_validate.init();
		}
	};
	exports.login = {
		bind : function(){
			exports.cache.$btn_login = jQuery(exports.config.btn_login_id);
			if(!exports.cache.$btn_login[0]) return false;
			exports.cache.$btn_login.on('click',function(){
				/** º”‘ÿ dialog */
				exports.login.dialog.init();
				/** º”‘ÿ tab */
				exports.tab.init(1);
				return false;
			});
		},
		dialog : {
			init : function(){
				/** 
				 * dialog
				 */
				exports.common.dialog({
					id : 'q-sign',
					title : exports.config.lang.M00008,
					witdh : 400,
					content : tools.status_tip('loading',exports.config.lang.M00001)
				});
			}
		
		},
		get_form : function(){
			return '<form action="javascript:;" id="q-login" class="fm-sign">' + 
				'<div class="form-group">' + 
					'<label for="l-user-email" class="form-icon"><span class="icon-envelope"></span></label>' + 
					'<input type="email" name="user[email]" id="l-user-email" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00005 + '" required tabindex="0" />' +
				'</div>' +
				'<div class="form-group">' + 
					'<label for="l-user-pwd" class="form-icon"><span class="icon-lock"></span></label>' + 
					'<input type="password" name="user[pwd]" id="l-user-pwd" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00006 + '" required tabindex="0" />' +
				'</div>' +
				'<div class="form-group">' + 
					'<div class="checkbox">' + 
						'<label for="l-remember"><input type="checkbox" name="user[remember]" id="l-remember" checked value="1" tabindex="0" />' + 
						exports.config.lang.M00009 + '</label>' +
					'</div>' + 
					'<div class="recover">' + 
						'<a href="' + exports.config.recover_pwd_url + '">' + exports.config.lang.M00012 + '</a>' +
					'</div>' + 
				'</div>' + 
				'<div class="form-group form-group-submit">' + 
					'<input type="hidden" name="type" value="login"/>' + 
					'<button type="submit" class="btn btn-primary full-width" tabindex="0">' + exports.config.lang.M00002 + '</button>' +
				'</div>' +
				'<div class="page-tip submit-tip hide"></div>' +
			'</form>';
		},
	};
	exports.register = {

		get_form : function(){
			return '<form action="javascript:;" id="q-register" class="fm-sign">' + 
				'<div class="form-group">' + 
					'<label for="r-user-name" class="form-icon"><span class="icon-user"></span></label>' + 
					'<input type="text" name="user[nickname]" id="r-user-name" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00004 + '" required />' +
				'</div>' +
				'<div class="form-group">' + 
					'<label for="r-user-email" class="form-icon"><span class="icon-envelope"></span></label>' + 
					'<input type="email" name="user[email]" id="r-user-email" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00005 + '" required />' +
				'</div>' +
				'<div class="form-group">' + 
					'<label for="r-user-pwd" class="form-icon"><span class="icon-lock"></span></label>' + 
					'<input type="password" name="user[pwd]" id="r-user-pwd" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00006 + '" required />' +
				'</div>' +
				'<div class="form-group">' + 
					'<label for="r-user-pwd-again" class="form-icon"><span class="icon-lock"></span></label>' + 
					'<input type="password" name="user[pwd-again]" id="r-user-pwd-again" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00007 + '" required />' +
				'</div>' +
				'<div class="form-group form-group-submit">' + 
					'<input type="hidden" name="type" value="register"/>' + 
					'<button type="submit" class="btn btn-primary full-width">' + exports.config.lang.M00003 + '</button>' +
				'</div>' +
				'<div class="page-tip submit-tip hide"></div>' +
			'</form>';
		}
	};
	exports.common = {
		/** 
		 * µØ≥ˆ≤„
		 */
		dialog : function(args,action){
			var cw = document.querySelector('.grid-container').clientWidth;
			if(cw <= 400 && !args.quickClose) args.width = cw - 80;
			var set_content = function(){
				if(args.id){
					dialog.get(args.id).content(args.content);
				}else{
					exports.cache.dialog.content(args.content);
				}
			},
			retry_set = function(){
				exports.cache.dialog = dialog(args).show();
			},
			set_title = function(){
				if(args.title){
					if(args.id){
						dialog.get(args.id).title(args.title);
					}else{
						exports.cache.dialog.title(args.title);
					}
				}
			},
			action = function(){
				if(action === 'hide' || action === 'close'){
					if(args.id){
						dialog.get(args.id).close().remove();
					}else{
						exports.cache.dialog.close().remove();
					}
				}
			};
			/** try set title/content, because we dont know dialog has been closed or not */ 
			try{
				set_title();
				set_content();
				action();
			}catch(e){
				retry_set();
			}
		},
		tpl : function(){
			return '<div id="q-sign-container"><div id="q-sign-loading" class="page-tip">' + tools.status_tip('loading',exports.config.lang.M00001) + '</div>' + 
			'<dl id="q-sign" class="hide">' + 
				'<dt class="q-sign-title"><span class="icon-user"></span><span class="after-icon">' + exports.config.lang.M00002 + '</span></dt>' + 
				'<dd class="q-sign-body">' + exports.login.get_form() + '</dd>' + 
				'<dt class="q-sign-title"><span class="icon-user-add"></span><span class="after-icon">' + exports.config.lang.M00003 + '</span></dt>' + 
				'<dd class="q-sign-body">' + exports.register.get_form() + '</dd>' + 
			'</dl></div>';
		}
	
	};
	exports.logged = {
		init : function(){
			jQuery('#sign').html(jQuery(exports.logged.tpl()));
		},
		tpl : function(){
			return '<a href="' + js_request['user']['posts_url'] + '" class="logged" target="_blank">' + 
				'<img src="' + js_request['user']['avatar_url'] + '" alt="'
				js_request['user']['display_name'] + '" class="avatar"/>' +
				'<span class="tx">' + js_request['user']['display_name'] + '</span>' + 
			'</a>';
		}
		
	};
});