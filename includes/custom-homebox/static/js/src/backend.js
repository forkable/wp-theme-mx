define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools');
	exports.init = function(){
		jQuery(document).ready(function(){
			bind();
		});
	}
	exports.config = {
		prefix_item_id : '#theme_custom_homebox-item-',
		items_id : '.theme_custom_homebox-item',
		add_id : '#theme_custom_homebox-add',
		control_container_id : '#theme_custom_homebox-control',
		tpl : ''
	}
	function bind(){
		add();
		del(jQuery(exports.config.items_id));
		
	}
	function add(){
		var $add = jQuery(exports.config.add_id),
			$control_container = jQuery(exports.config.control_container_id);
		if(!$add[0]) return false;
		$add.on('click',function(){
			var $tpl = jQuery(exports.config.tpl.replace(/\%placeholder\%/ig,get_random_int(100,999)));
			del($tpl);
			$control_container.before($tpl);
			$tpl.find('input').eq(0).focus();
		});
	
	}
	function del($tpl){
		$tpl.find('.delete').on('click',function(){
			jQuery(jQuery(this).data('target')).css('background','#d54e21')
			.fadeOut('slow',function(){
				jQuery(this).remove();
			})
		})
	}
	function get_random_int(min, max) {
		return new Date().getTime() + '' + (Math.floor(Math.random() * (max - min + 1)) + min);
	}
});