define(function(require, exports, module){
	var $ = require('modules/jquery'),jQuery = $;

	exports.config = {
		face_btn_id : '#comment-face li',
		face_box_id : '.comment-face-box',
		comment_id : '#comment',
		faces : '',
		faces_url : ''
	};
	exports.init = function(){
		jQuery(document).ready(function(){
			exports.bind();
		});
	};
	exports.cache = {};
	exports.bind = function(){
		exports.cache.$face_btn = jQuery(exports.config.face_btn_id);
		if(!exports.cache.$face_btn[0]) return false;
		/** 
		 * set cache
		 */
		exports.cache.$comment = jQuery(exports.config.comment_id);
		
		exports.cache.$face_btn.on('click',function(e){
			e.preventDefault();
			e.stopPropagation();
			var $face_btn = jQuery(this);
			$face_btn.each(function(i){
				var $this = jQuery(this),
					$box = $this.find(exports.config.face_box_id),
					face_len = exports.config.faces.length,
					as = '',
					$as;
					
				if(!$box.has('a')[0]){
					for(var i=0;i<face_len;++i){
						as += exports.hook.tpl_image(exports.config.faces_url + exports.config.faces[i],exports.config.faces[i]);
					}
					$as = jQuery(as);
					$box.append($as);
				}
				/** 
				 * show box or hide box
				 */
				$box.toggle();
				// $box.is(':hidden') ? $box.slideDown('fast') : $box.slideUp('fast');
				/** 
				 * when click the other ele, just to hide box
				 */
				jQuery(document).off('click').on('click',function(e){
					var $target = jQuery(e.target);
						
					if($target != $box && !$box.is(':hidden')){
						$box.toggle();
					}
				});
				
				$box.find('a').off().on('click',function(){
					var $this = jQuery(this);
					var old_value = exports.cache.$comment.val(),
						face_id = $box.hasClass('type-text') ? $this.data('id') : '[' + $this.data('id') + ']';
					exports.cache.$comment.focus();
					exports.cache.$comment.val(old_value + ' ' + face_id + ' ');
				});
			});
		});
	};
	
	exports.hook = {
		tpl_image : function(img_src,id){
			if(!img_src || !id) return false;
			var content = '<a href="javascript:;" data-id="' + id + '"><img src="' + img_src + '" alt="" /></a>';
			return content;
		},
		tpl_text : function(id){
			if(!img_src || !id) return false;
			var content = '<a href="javascript:;" data-id="' + id + '">' + id + '</a>';
			return content;
		}
	
	};

});