define(function(require, exports, module){
	'use strict';
	var $ = require('modules/jquery'),
		jQuery = $,
		dialog = require('modules/jquery.dialog'),
		tools = require('modules/tools');
	require('modules/jquery.filedrop');
	exports.init = function(){
		jQuery(document).ready(function(){
			exports.bind();
		});
	}
	
	
	exports.config = {
		items_id : '.slidebox-item',
		items_prefix_id : '#slidebox-item-',
		add_btn_id : '#slidebox-add',
		control_box_id : '#slidebox-control',
		del_btn_id : '.slidebox-del',
		file_btn_id : '.slidebox-file',
		placeholder_pattern : /\%placeholder\%/ig,
		tpl : '',
		process_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Server error or network is disconnected.'
		}
	}
	exports.cache = {}
	
	exports.bind = function(){
		exports.cache.$item = $(exports.config.items_id);
		exports.cache.$add_btn = $(exports.config.add_btn_id);
		exports.cache.$control_box = $(exports.config.control_box_id);
		exports.cache.$del_btns = $(exports.config.del_btn_id);
		exports.cache.$file_btns = $(exports.config.file_btn_id);
		/** 
		 * bind del event for first init
		 */
		exports.event_del(exports.cache.$del_btns);
		/** 
		 * bind add event
		 */
		exports.event_add(exports.cache.$add_btn);
		/** 
		 * bind upload event
		 */
		var upload = new exports.file();
		upload.$item = exports.cache.$item;
		upload.init();
		
	}
	exports.file = function(){
		this.$item = '';
		
		this.init = function(){
			var $item = this.$item,
				$file = $item.find('.slidebox-file'),
				$area = $item.find('.slidebox-upload-area'),
				$tip = $item.find('.slidebox-upload-tip'),
				$url = $item.find('.slidebox-img-url');
			$file.filedrop({
				fallback_id : $file[0].id,
				url : exports.config.process_url,
				paramname : 'img',
				uploadStarted : function(i, file, len){
					$area.hide();
					$tip.html(tools.status_tip('loading',exports.config.lang.M00001)).show();
				},
				uploadFinished: function(i, file, data, time) {
					if(data && data.status === 'success'){
						$url.val(data.url);
						$tip.html(tools.status_tip('success',data.msg));
					}else if(data && data.status === 'error'){
						$tip.html(tools.status_tip('error',data.msg));
					}else{
						$tip.html(tools.status_tip('error',data.msg));
					}
					$area.show();
					$file.val('');
				},
				error: function(err, file, i){
					$tip.html(tools.status_tip('error',err));
					$area.show();
					$file.val('');
				}
			});
		}
	}
	exports.event_add = function($add){
		$add.on('click',function(){
			var tpl = exports.config.tpl.replace(exports.config.placeholder_pattern,exports.get_next_id_number());
			exports.cache.$new_item = $(tpl).hide();
			exports.event_del(exports.cache.$new_item.find('.slidebox-del'));
			/** 
			 * bind upload event
			 */
			var upload = new exports.file();
			upload.$item = exports.cache.$new_item;
			upload.init();
			
			exports.cache.$control_box.before(exports.cache.$new_item);
			exports.cache.$new_item.fadeIn().find('input').eq(0).focus();
			
		});
	}
	exports.event_del = function($del){
		$del.on('click',function(){
			var $this = $(this),
				id = $this.data('id');
			$(exports.config.items_prefix_id + id).fadeOut('1',function(){
				$(this).remove();
			}).css({
				'background-color':'#d54e21'
			});
		});
	}
	exports.get_next_id_number = function(){
		exports.cache.$items = $(exports.config.items_id);
		if(!exports.cache.$items[0]) return 1;
		return exports.cache.$items.eq(exports.cache.$items.length - 1).data('placeholder') + 1;
	}

	exports.color_tpl = function(curr_color){
		var tpl = '';
		for(var i in exports.config.preset_colors){
			var color = exports.config.preset_colors[i],
				curr_class = curr_color == color ? ' class="current" ' : '';
			tpl += '<a href="javascript:void(0);" style="background-color:#' + color + '" data-color="' + color + '" ' + curr_class +'></a>';
		}
		tpl = '<div id="colorful-selector">' + tpl + '</div>';
		return tpl;
		
	}
	
	exports.dialog = function(args){
		args.quickClose = true;
		var set_content = function(){
			if(args.id){
				dialog.get(args.id).content(args.content).show(exports.cache.$current_btn[0]);
			}else{
				exports.cache.dialog.content(args.content).show(exports.cache.$current_btn[0]);
			}
		},
		retry_set = function(){
			exports.cache.dialog = dialog(args).show(exports.cache.$current_btn[0]);
		};
		try{
			set_content();
		}catch(e){
			retry_set();
		}
	}
	
});