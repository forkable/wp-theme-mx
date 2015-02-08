/**
 * theme-comment-ajax
 *
 * @version 2.0.0
 * @author KM@INN STUDIO
 */
define(function(require, exports, module){
	'use strict';

	var dialog 		= require('modules/jquery.dialog'),
		$ 			= require('modules/jquery'),
		jQuery		= $,
		js_request 	= require('theme-cache-request'),
		tools 		= require('modules/tools');
					require('modules/jquery.validate');
	

	
	exports.config = {
		process_url : '',
		
		lang : {
			M00001 : 'Loading, please wait...', 
			M00002 : 'Commented successfully, thank you!',
			M00003 : 'Tip',
			E00001 : 'Server error or network is disconnected.',
			validate : {
				required : 'This field is required.',
				email : 'Please enter a valid email address.'
			}
		},
		
		comm_list_id : '.comment-list',
		comm_respond_id : '#respond',
		comm_frm_id : '#commentform',
		comm_wrap_id : '.comment-wrapper',
		
		comm_pagi_id : '.comment-pagination',
		
		comm_tmp_div_id : '#comment-tmp-div'
		
	
	};
	exports.cache = {
		$comm_list : false,
		$comm_respond : false,
		$comm_frm : false,
		$comm_pagi : false,
		
		$comm_wrap : false,
		
		$comm_tmp_div : false,
		
		data : []
	};
	
	exports.init = function(){
		$(document).ready(function(){
			/** 
			 * init the cache
			 */
			exports.cache.$comm_list = $(exports.config.comm_list_id);
			exports.cache.$comm_pagi = $(exports.config.comm_pagi_id);
			exports.cache.$comm_wrap = $(exports.config.comm_wrap_id);
			
			exports.comm_add.bind();
			exports.comm_pagi.bind();
		});
	};
	exports.hook ={
		dialog : false,
		tip : function(status,content,title,timeout){
			exports.hook.dialog = dialog({
				id : 'comm',
				title : false,
				fixed : true,
				quickClose : true
			});
			if(title){
				exports.hook.dialog.title(title);
			}else{
				exports.hook.dialog.title(false);
			}
			exports.hook.dialog.content(tools.status_tip(status,content)).show();
			if(timeout){
				var dialog_hide = function(){
					exports.hook.dialog.close();
				}
				setTimeout(dialog_hide,timeout);
			}
		},
		/**
		 * exports.hook.get_cache
		 *
		 * @param string key
		 * @return mixed
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		get_cache : function(key,group){
			if(!key) return false;
			key = encodeURIComponent(key);
			if(group) return exports.cache.data[group] ? exports.cache.data[group][key] : false;
			return exports.cache.data[key];
		},
		/**
		 * exports.hook.set_cache
		 *
		 * @param string key
		 * @param mixed data
		 * @param string group
		 * @return mixed
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		set_cache : function(key,data,group){
			if(!key) return false;
			key = encodeURIComponent(key);
			if(group){
				if(!exports.cache.data[group]) exports.cache.data[group] = [];
				exports.cache.data[group][key] = data;
			}else{
				exports.cache.data[key] = data;
			}
		}
	};
	/** 
	 * exports.comm_add
	 */
	exports.comm_add = {
		
		bind : function(){
			exports.cache.$comm_respond = $(exports.config.comm_respond_id);
			if(!exports.cache.$comm_respond[0]) return;
			exports.cache.$comm_frm = exports.cache.$comm_respond.find(exports.config.comm_frm_id);
			/** 
			 * bind when click the comment count, jump to respond form
			 */
			exports.comm_add.hook.click_to_add_comment();

			exports.cache.$comm_frm.validate({
				submitHandler : function() {
					exports.cache.$submit = exports.cache.$comm_frm.find(':submit');
					var ajax_url_data = {
						'theme-nonce' : js_request['theme-nonce']
					};
					exports.comm_add.hook.brefore_send();
					jQuery.ajax({
						url : exports.config.process_url + '&' + jQuery.param(ajax_url_data),
						type : 'post',
						dataType : 'html',
						data : exports.cache.$comm_frm.serialize()
					}).done(function(data){
						exports.comm_add.hook.done(data);
					}).fail(function(jqXHR,textStatus,errorThrown){
						exports.comm_add.hook.fail(textStatus);
					});
				}
			});
		},
		/** 
		 * exports.comm_add.hook
		 */
		hook : {
			dialog : false,
			/** 
			 * click_to_add_comment
			 */
			click_to_add_comment : function(){
				var $go_to_respond = $('a[href="#respond"]');
				if(!$go_to_respond[0]) return false;
				$go_to_respond.on('click',function(){
					$scroll_ele.animate({
						scrollTop : exports.cache.$comm_respond.offset().top
					},300,function(){
						location.hash = $go_to_respond.attr('href');
						tools.auto_focus(exports.cache.$comm_frm);
					});
					return false;
				});
			},
			
			brefore_send : function(){
				exports.hook.tip('loading',exports.config.lang.M00001);
				/** 
				 * add a attr for cache the submit btn
				 */
				exports.cache.$submit
					.attr('disabled',true)
					.addClass('disabled');
			},
			done : function(data){
				var is_json = true;
				try{
					data = jQuery.parseJSON(data);
					// alert('ok');
				}catch(e){
					is_json = false;
					data = data;
				}
				/** 
				 * is json
				 */
				if(is_json){
					if(data && data.status && data.status === 'success'){
						exports.comm_add.hook.append(data);
						exports.cache.$comm_respond.find('textarea').val('');
						exports.hook.tip('success',data.msg,exports.config.lang.M00003,3000);
					}else if(data && data.status && data.status === 'error'){
						exports.hook.tip('error',data.msg,exports.config.lang.M00003);
					}else{
						exports.hook.tip('error',exports.config.lang.E00001,exports.config.lang.M00003);
					}
				}else{
					exports.hook.tip('error',data,exports.config.lang.M00003);
				}
				var c = function(){
					exports.cache.$submit
					.removeAttr('disabled')
					.removeClass('disabled');
				};
				setTimeout(c,5000);
			},
			fail : function(){
				exports.cache.$submit
					.removeAttr('disabled')
					.removeClass('disabled');
			},
			/**
			 * append
			 *
			 * @param object
			 * @return 
			 * @version 1.0.0
			 * @author KM@INN STUDIO
			 */
			append : function(data){
				var $comment = $(data.des.comment);
				/** 
				 * replace the lazyload
				 */
				$comment.find('img[data-original]').each(function(i){
					var $this = $(this);
					$this.attr('src',$this.data('original'));
				});
				/** 
				 * check if is a reply comment
				 */
				if(data && data.des && data.des.comment_parent){
					// var comm_pid = data.des.comment_parent;
					$comment.hide();
					exports.cache.$comm_respond.before($comment);
					$comment.slideDown();
				/** 
				 * is new comment
				 */
				}else{
					exports.cache.$comm_list = exports.cache.$comm_list || $(exports.config.comm_list_id);
					if(!exports.cache.$comm_list[0]) return false;
					$comment.hide();
					exports.cache.$comm_list.append($comment);
					exports.cache.$comm_list.slideDown();
					$comment.slideDown();
				}
				if(!tools.in_screen($comment)){
					$scroll_ele.animate({
						scrollTop : $comment.offset().top - 20
					})
				}
			}
		}
	};
	/** 
	 * exports.comm_reply
	 */
	exports.comm_reply = {
		addComment : {
			moveForm : function(commId, parentId, respondId, postId) {
				/** 
				 * get or set the cache
				 */
				exports.cache.$comm_tmp_div = exports.cache.$comm_tmp_div || $(exports.config.comm_tmp_div_id);
				/** 
				 * create a tmp div before the $respond
				 */
				exports.comm_reply.addComment.tmp_create();
				
				/** 
				 * move $respond to reply comment position, 
				 * and modfiy the comment_parent & comment_post_ID
				 * and show the cancel-comment-reply-link
				 */
				exports.cache.$comm_respond.find('#comment_parent').val(parentId);
				exports.cache.$comm_respond.find('#comment_post_ID').val(postId)
				exports.cache.$comm_respond.find('#cancel-comment-reply-link').show();
				// exports.cache.$comm_respond.hide();
				$('#' + commId).after(exports.cache.$comm_respond);
				// exports.cache.$comm_respond.slideDown();

				/** 
				 * auto focus
				 */
				tools.auto_focus(exports.cache.$comm_frm);
				
				return false;
			},
			/** 
			 * tmp_create
			 */
			tmp_create : function(){
				/** 
				 * create a tmp div before the $respond
				 */
				if(!exports.cache.$comm_tmp_div[0]){
					exports.cache.$comm_tmp_div = $('<div></div>').attr('id',exports.config.comm_tmp_div_id.substr(1)).hide();
					exports.cache.$comm_respond.before(exports.cache.$comm_tmp_div);
					/** 
					 * bind cancel-comment-reply-link event
					 */
					exports.cache.$comm_respond.find('#cancel-comment-reply-link').on('click',function(){
						var $this = $(this);
						$this.hide();
						/** 
						 * move frm back
						 */
						exports.comm_reply.addComment.frm_restore();
						return false;
					});
				}
			
			},
			/** 
			 * frm_restore
			 */
			frm_restore : function(){
				/** 
				 * move $respond back
				 */
				exports.cache.$comm_tmp_div.after(exports.cache.$comm_respond);
				/** 
				 * set comment_parent to 0
				 */
				exports.cache.$comm_respond.find('#comment_parent').val('0');
				/** 
				 * hide the cancel-comment-reply-link
				 */
				exports.cache.$comm_respond.find('#cancel-comment-reply-link').hide();
			}
		}
	};
	
	/** 
	 * comm_pagi
	 */
	exports.comm_pagi = {
		
		bind : function(){
			if(!exports.cache.$comm_pagi[0]) return false;
			
			exports.cache.$comm_pagi.on('click','a',function(){
				/** 
				 * move frm back
				 */
				exports.comm_reply.addComment.tmp_create()
				exports.comm_reply.addComment.frm_restore();
				
				var $a = $(this),
					$comments = exports.hook.get_cache('list_' + $a.attr('href'),'comm_pagi'),
					$pagination = exports.hook.get_cache('pagi_' + $a.attr('href'),'comm_pagi');
				
				if($comments && $pagination){
					exports.cache.$comm_list.html($comments);
					exports.cache.$comm_pagi.html($pagination);
					/** 
					 * bind the ajax login
					 */
					// wm.init(exports.cache.$comm_list.find('.comment-reply-login'));
					/** 
					 * scroll
					 */
					exports.comm_pagi.hook.scroll();					
					// exports.comm_pagi.bind();
					return false;
				}
				/** 
				 * if no cache, start ajax to get new data
				 */
				exports.comm_pagi.hook.brefore_send();
				jQuery.ajax({
					url : exports.config.process_url + '&theme-nonce=' + js_request['theme-nonce'],
					dataType : 'json',
					data : {
						type : 'comm_pagination',
						url : $a.attr('href')
					}
				}).done(function(data){
					exports.comm_pagi.hook.done(data,$a);
				}).fail(function(){
					exports.comm_pagi.hook.fail();
				});
				return false;
			});
		},
		
		hook : {			

			brefore_send : function(){
				exports.hook.tip('loading',exports.config.lang.M00001);
				exports.cache.$comm_wrap.fadeTo('slow','0.5');
			},
			
			done : function(data,$a){
				if(data && data.status && data.status === 'success'){
					var $comments = $(data.des.comments),
						$pagination = $($(data.des.pagination).html());
					/** 
					 * replace the lazyload
					 */
					$comments.find('img[data-original]').each(function(i){
						var $this = $(this);
						$this.attr('src',$this.data('original'));
					});					/** 
					 * set to cache
					 */
					exports.hook.set_cache('list_' + $a.attr('href'),$comments,'comm_pagi');
					exports.hook.set_cache('pagi_' + $a.attr('href'),$pagination,'comm_pagi');
					/** 
					 * inner html
					 */
					exports.cache.$comm_list.html($comments);
					exports.cache.$comm_pagi.html($pagination);
					/** 
					 * bind the ajax login
					 */
					// wm.init(exports.cache.$comm_list.find('.comment-reply-login'));
					
					/** 
					 * rebind
					 */
					// exports.comm_pagi.bind();
					/** 
					 * scroll
					 */
					exports.comm_pagi.hook.scroll();
					/** 
					 * close the dialog
					 */
					exports.hook.dialog.close();
					
				}else if(data && data.status && data.status === 'error'){
					exports.hook.tip('error',data.msg,exports.config.lang.M00003);
				}else{
					exports.hook.tip('error',exports.config.lang.E00001,exports.config.lang.M00003);
				}
				exports.cache.$comm_wrap.fadeTo('fast','1');
			},
			
			fail : function(){
				exports.hook.tip('error',exports.config.lang.E00001,exports.config.lang.M00003);
				exports.cache.$comm_wrap.fadeTo('fast','1');
			},
			/** 
			 * scroll to top
			 */
			scroll : function(){
				if(!tools.in_screen(exports.cache.$comm_pagi)){
					$scroll_ele.animate({
						scrollTop : exports.cache.$comm_pagi.offset().top - 40
					});
				}
		}
		}
	};

	var $scroll_ele = navigator.userAgent.toLowerCase().indexOf('webkit') === -1 ? $('html') : $('body');
	
	window.addComment = exports.comm_reply.addComment;
});