define(function(require, exports, module){
	'use strict';

	var js_request 	= require('theme-cache-request'),
		tools 		= require('modules/tools');
	exports.config = {
		
	};
	var cache = {},
		config = exports.config;

	exports.init = function(){
		tools.ready(function(){
			tab_bind();
		});
	};

	function tab_bind(){
		cache.$tabs = I('pm-tab');
		cache.$dialogs = document.querySelectorAll('.pm-dialog');
		cache.$tab_items = cache.$tabs.querySelectorAll('a');
		cache.$dialog_pm_new = I('pm-new');
		cache.$pm_new_receiver_id = I('pm-new-receiver-id');
		
		for(var i=0, len=cache.$tab_items.length; i<len; i++){
			event_switch_tab(i);
		}
	}
	function event_switch_tab(i){
		function helper(){
			var $dialog = I(this.getAttribute('data-target'));
			/** hide other dialog and show current dialog*/
			for(var i=0,len=cache.$dialogs.length;i<len;i++){
				if(cache.$dialogs[i] != $dialog){
					$dialog.style.display = 'none';
				}else{
					$dialog.style.display = 'block'
				}
				/** focus current dialog */
				if(cache.$dialogs[i] == cache.$dialog_pm_new){
					cache.$pm_new_receiver_id.focus();
				}else{
					cache.$active_dialog_content = cache.$dialogs[i].querySelector('.pm-dialog-conteng');
					cache.$active_dialog_content.focus();
				}
			}
		}
		cache.$tab_items[i].addEventListener('click',helper);
		
	}
	function I(e){
		return document.getElementById(e);
	}
});