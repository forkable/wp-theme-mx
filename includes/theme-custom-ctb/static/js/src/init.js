define(function(require, exports, module){
	
	var $ = require('modules/jquery'),
		tools = require('modules/tools'),
		dialog = require('modules/jquery.dialog'),
		js_request 	= require('theme-cache-request');
	require('modules/jquery.validate');
	require('modules/jquery.validate.lang.{locale}');
	
	exports.config = {
		fm_id : '#contribution',
		tip_id : '#ctb-tip',
		file_container_id : '#ctb-file-container',
		file_id : '#ctb-file',
		file_tip_id : '#ctb-file-tip',
		img_pre_id : '#ctb-attach-pre',
		attach_id_id : '#ctb-attach-id',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Sorry, server error please try again later.'
		},
		process_url : '',
		upload_max_filesize : 2,
		allow_filetype : ['.jpg','.jpeg','.png','.gif']
	};
	exports.cache = {};
	exports.init = function(){
		$(document).ready(function(){
			exports.bind();
			exports.tags.init();
			exports.category.init();
		});
	};
	
	exports.bind = function(){
		exports.cache.$fm = $(exports.config.fm_id);
		if(!exports.cache.$fm[0]) return false;
		/** set tip elems and hide*/
		exports.cache.$tip = $(exports.config.tip_id).hide();
		/** set .form-group-submit to show */
		exports.cache.$fm_gp_sm = exports.cache.$fm.find('.form-group-submit').show();
		exports.cache.$fm_sm = exports.cache.$fm.find(':submit');
		/** init file upload */
		if(exports.support_h5()) exports.file.init();
		/** post */
		exports.post.init()
		// console.log(exports.support_h5());
	};
	/** 
	 * file
	 */
	exports.file = {
		init : function(){
			exports.cache.$file = $(exports.config.file_id);
			if(!exports.cache.$file[0]) return false;
			/** file drop */
			require.async(['modules/jquery.filedrop'],function(_a){
				exports.cache.$file.filedrop({
					paramname : 'img',
					maxfiles : 1,
					maxfilesize : exports.config.upload_max_filesize,
					url : exports.config.process_url + '&' + $.param({
						'theme-nonce' : js_request['theme-nonce'],
						'type' : 'upload'
					}),
					allowedfileextensions : exports.config.allow_filetype,
					uploadStarted : function(i,file,len){
						/** set cache */
						if(!exports.cache.$file_container) exports.cache.$file_container = $(exports.config.file_container_id);
						if(!exports.cache.$img_pre_container) exports.cache.$img_pre_container = $(exports.config.img_pre_id);
						if(!exports.cache.$attach_id) exports.cache.$attach_id = $(exports.config.attach_id_id);
						
						exports.cache.$img_pre_container.find('img').remove();
						exports.cache.$attach_id.val('');
						exports.cache.$file_container.hide();
						exports.file.tip('loading',exports.config.lang.M00001);
					},
					beforeEach : function(file){
						// exports.file.upload(file);
					},
					uploadFinished : function(i,file,data){
						exports.file.done(i,file,data);
					},
					error: function(err, file) {
						var error_msg;
						switch(err) {
							case 'BrowserNotSupported':
								error_msg = 'Your browser does not support HTML5 file uploads!';
								break;
							case 'TooManyFiles':
								error_msg = 'Too many files! Please select 5 at most! (configurable)';
								break;
							case 'FileTooLarge':
								error_msg = file.name+' is too large! Please upload files up to ' + exports.config.upload_max_filesize + 'mb.';
								break;
							default:
								error_msg = exports.config.lang.E00001;
								break;
						}
						exports.file.tip('error',error_msg);
						// exports.cache.$file_container.show();
					},
					afterAll : function(){
						// exports.file.tip('success',exports.config.lang.M00002);
						exports.cache.$file_container.show();
						exports.cache.$file.val('');
					}
				});
			});
		},
		upload : function(file){
			var reader = new FileReader();
			reader.onload = function(e){
				
			};
			reader.readAsDataURL(file);
		},
		done : function(i,file,data){
			// console.log('done');
			if(data && data.status === 'success'){
				exports.file.tip('success',data.msg);
				/** set base64 data to preview */
				var reader = new FileReader();
				reader.onload = function(e){
					exports.cache.$attach_id.val(data.attach_id);
					/** set img to container */
					var $pre_img = $('<img src="' + e.target.result + '" alt="" title="' + exports.config.lang.M00003 + '"/>').on('click',function(){
						exports.cache.$attach_id.val('');
						$(this).remove();
						exports.file.tip('hide');
					});
					exports.cache.$img_pre_container.html($pre_img);
				};
				reader.readAsDataURL(file);
				
			}else if(data && data.status === 'error'){
				exports.file.tip('error',data.msg);
			}else{
				exports.file.tip('error',exports.config.lang.E00001);
			}
			// exports.cache.$file_container.show();
		},
		
		tip : function(t,s){
			if(!exports.cache.$file_tip) exports.cache.$file_tip = $(exports.config.file_tip_id);
			if(t === 'hide'){
				exports.cache.$file_tip.hide();
			}else{
				exports.cache.$file_tip.html(tools.status_tip(t,s)).show();
			}
		}
	
	};
	exports.support_h5 = function(){
		return window.File && window.FileReader && window.FileList && window.Blob;
	};
	/** 
	 * tip
	 */
	exports.tip = function(t,s){
		if(!exports.cache.$tip) exports.cache.$tip = $(exports.config.tip_id);
		if(t === 'hide'){
			exports.cache.$tip.hide();
		}else{
			exports.cache.$tip.html(tools.status_tip(t,s)).show();
		}
	}
	/** 
	 * post
	 */
	exports.post = {
		init : function(){
			tools.auto_focus(exports.cache.$fm);
			var m = new tools.validate();
				m.process_url = exports.config.process_url + '&' + $.param({
					'theme-nonce' : js_request['theme-nonce'],
					'type' : 'post'
				});
				m.loading_tx = exports.config.lang.M00001;
				m.error_tx = exports.config.lang.E00001;
				m.$fm = exports.cache.$fm;
				m.init();

		}
	};
	exports.category = {
		init : function(){
			var _this = this;
			exports.cache.$cats_btns = $('.ctb-cat');
			exports.cache.$cats_inputs = exports.cache.$cats_btns.find('input');
			if(!exports.cache.$cats_btns[0]) return false;
			/** check first load */
			exports.cache.$cats_inputs.each(function(){
				_this.change_class($(this));
			});
			exports.cache.$cats_inputs.on('change',function(){
				_this.change_class($(this));
			});
		},
		change_class : function($ele){
			if($ele.prop('checked')){
				$ele.parent().addClass('btn-primary');
			}
			exports.cache.$cats_inputs.not($ele).parent().removeClass('btn-primary');
		}
	};
	/** 
	 * tags
	 */
	exports.tags = {
		init : function(){
		
			
			exports.cache.$tags_add_btn = $('#ctb-tags-add-btn');
			exports.cache.$tags_container = $('#ctb-tags-container');
			exports.cache.$tags_add_input = $('#ctb-tags-add-input');
			exports.cache.$tags_add_container = $('#ctb-tags-add-container');
			if(!exports.cache.$tags_add_btn[0] || !exports.cache.$tags_container[0] || !exports.cache.$tags_add_input[0]) return false;
			
			/** check entry key */
			exports.cache.$tags_add_input.on('keydown',function(e){
				if(e.keyCode == 13){
					exports.tags.append_container();
					
					e.returnvalue = false;
					return false;
				}
			});
			exports.cache.$tags_add_btn.on('click',function(){
				exports.tags.append_container();
				return false;
			});
			exports.tags.preset.init();
		},
		append_container : function(){
			if($.trim(exports.cache.$tags_add_input.val()) === ''){
				exports.cache.$tags_add_input.focus();
				return false;
			}
			/** add tpl to container */
			var $tag = $(exports.tags.tpl({
				name : $.trim(exports.cache.$tags_add_input.val())
			})).on('click',function(){
				$(this).remove();
				// console.log(exports.cache.$tags_container.find('a').length);
				exports.tags.is_max_tags();
			});
			
			
			/** append to container */
			exports.cache.$tags_container.append($tag);
			/** reset value 4 input */
			exports.cache.$tags_add_input.val('');
			/** calculate btns number & refocus */
			exports.tags.is_max_tags() || exports.cache.$tags_add_input.focus();
		},
		/** calculate btns number */
		is_max_tags : function(){
			if(exports.cache.$tags_container.find('a').length >= exports.config.max_tags_number){
				exports.cache.$tags_add_container.hide();
				return true;
			}else{
				exports.cache.$tags_add_container.show();
				return false;
			}
		},
		preset : {
			init : function(){
				exports.cache.$preset_tags = $('.ctb-tags-preset');
				if(!exports.cache.$preset_tags[0]) return false;
				exports.cache.$preset_tags.on('change',function(){
					exports.tags.preset.append_container($(this));
				});
				
				exports.cache.$preset_tags.each(function(){
					if($(this).prop('checked')){
						exports.tags.preset.append_container($(this));
					}
				});
			},
			append_container : function($this){
				/** if checked, check the max tags and append to tags container */
				if($this.prop('checked')){
					/** is max tags */
					if(exports.tags.is_max_tags()){
						var d = dialog({
							title : false,
							content : tools.status_tip('error',exports.config.lang.M00004),
							quickClose : true
						}).show(exports.cache.$tags_container.find('a').eq(-1)[0]);
						$this.prop('checked',false);
					/** hide this preset */	
					}else{
						$('#ctb-comm-tag-label-' + $this.data('tagId')).addClass('btn-primary');
						/** add tpl to container */
						var $tag = $(exports.tags.tpl({
							name : $this.val()
						})).attr({
							id : 'ctb-tag-fr-comm-' + $this.data('tagId')
						}).on('click',function(){
							$(this).remove();
							$('#ctb-comm-tag-label-' + $this.data('tagId')).removeClass('btn-primary');
							$this.prop('checked',false);
							exports.tags.is_max_tags();
							/** check max tags number */
						});
						/** append to container */
						exports.cache.$tags_container.append($tag);
						exports.tags.is_max_tags();
					}
				}else{
					$('#ctb-comm-tag-label-' + $this.data('tagId')).removeClass('btn-primary');
					$('#ctb-tag-fr-comm-' + $this.data('tagId')).remove();
					exports.tags.is_max_tags();
				}
				
			}
			
		},
		tpl : function(args){
			if(!args) args = {};
			var name = args.name ? args.name : '';
			return '<a href="javascript:void(0);" class="ctb-tags btn btn-small">' + 
				'<span class="icon-tag"></span><span class="after-icon">' + name + '</span>' + 
				'<input type="hidden" name="tags[]" value="' + name + '"/>' + 
			'</a>';
		}
	};
});