define(function(require, exports, module){
	'use strict';
	var dialog 		= require('modules/jquery.dialog'),
		js_request 	= require('theme-cache-request'),
		tools 		= require('modules/tools');
					require('modules/jquery.validate');
	exports.init = function(){
		exports.list.init();
	};
	exports.config = {
		show_comm_id : '.quick-comment',
		comm_box_id : '#comment-box-',
		comm_tip_id : '.comment-tip',
		comms_container_id : '.comments-container',
		respond_container_id : '#respond-container-',
		add_comm_btns_id : '.add-comment',
		add_comm_btn_id : '.add-comment-',
		reply_comm_btn_id : '.reply',
		process_url : '',
		is_singular : false,
		lang : {
			M00001 : 'Loading, please wait...',
			M00002 : 'Commented successfully, thank you!',
			M00003 : 'Message',
			M00004 : 'No comment yet, you are first commenter.',
			M00005 : 'Comment',
			M00006 : 'Post comment',
			M00007 : 'Nickname',
			M00008 : 'Email',
			M00009 : 'Closing tip after 3s',
			M00010 : 'Reply',
			M00011 : 'M00011',
			M00012 : 'Website',
			E00001 : 'Server error or network is disconnected.'
		}
	};
	exports.cache = {
		$respond_container : false,
		$show_comms : false,
		$show_comm : false,
		$comm_box : false,
		$comm_tip : false,
		$respond_tip :false,
		post_id : false,
		posts : {},
		comm_box_datas : {}
	};
	/** 
	 * 评论列表函数集
	 */
	exports.list = {
		init : function($bind_ele){
			/** 获取显示评论列表的按钮并写入到缓存 Get show comm btn and set to cache */
			exports.cache.$show_comms = $(exports.config.show_comm_id);
			if(!exports.cache.$show_comms[0]) return false;
			if(!exports.cache.$show_comms.data('postId')) return false;
			/** 绑定点击 Bind click event */
			exports.list.bind(exports.cache.$show_comms);
		},
		/** 
		 * 绑定点击 list.bind
		 */
		bind : function($ele){
			var _this = this;
			if(exports.config.is_singular){
				if(location.hash.indexOf('#comment-') === 0){
					exports.$scroll_ele.animate({
						scrollTop : $(exports.config.comm_box_id + $ele.data('postId')).offset().top - 20
					})
				}
				$ele.on('click',function(){
					exports.$scroll_ele.animate({
						scrollTop : $('.comment-box').offset().top - 40
					},300);
					
				});
				_this.core($ele,false);
			}else{
				$ele.on('click',function(){
					_this.core($(this),true);
				});
			}
		},
		core : function($ele,click){
			/** 设置文章 ID Set post ID */
			exports.cache.post_id = $ele.data('postId');
			/** 设置post id 缓存 */
			if(!exports.cache.posts[exports.cache.post_id]) exports.cache.posts[exports.cache.post_id] = {};
			/** 设置评论盒子容器 */
			exports.cache.posts[exports.cache.post_id].$comm_box = $(exports.config.comm_box_id + exports.cache.post_id);
			/** 设置评论列表容器 */
			exports.cache.posts[exports.cache.post_id].$comm_container = exports.cache.posts[exports.cache.post_id].$comm_box.find(exports.config.comms_container_id);
			/** 设置评论提示 */
			exports.cache.posts[exports.cache.post_id].$comm_tip = exports.cache.posts[exports.cache.post_id].$comm_box.find(exports.config.comm_tip_id);
			/** 
			 * 显示评论列表
			 */
			if(click){
				if(exports.cache.posts[exports.cache.post_id].$comm_box.is(':hidden')){
					/** show comm box */
					exports.list.show_comm_box();
				/** 隐藏评论列表 */	
				}else{
					 exports.cache.posts[exports.cache.post_id].$comm_box.slideUp();
				}
			}else{
				exports.list.show_comm_box();
			}
		},
		/** 
		 * 提示 list.tip
		 */
		tip : function(t,s){
			if(t === 'hide'){
				exports.cache.posts[exports.cache.post_id].$comm_tip.hide();
			}else{
				exports.cache.posts[exports.cache.post_id].$comm_tip.html(tools.status_tip(t,s)).show();
			}
		},
		/** 
		 * 显示评论盒子 list.show_comm_box
		 */
		show_comm_box : function(){
			/** respond init */
			exports.respond.init();
			/** 获取缓存 */
			var cache_data = exports.list.get_data_from_cache(exports.cache.post_id);
			if(!cache_data){
				/** loading tip */
				//exports.list.tip('loading',exports.config.lang.M00001);
				exports.list.get_data_from_server.ajax.init();
			}
			exports.cache.posts[exports.cache.post_id].$comm_box.slideDown();
		},
		/** 
		 * 从缓存中获取评论信息 list.get_data_from_cache
		 */
		get_data_from_cache : function(){
			/** 缓存为空返回false */
			// console.log(exports.cache.posts[exports.cache.post_id]);
			if(!exports.cache.posts[exports.cache.post_id].$comm_box_datas) return false;
			/** 通过缓存输出 */
			return true;
			// exports.cache.posts[exports.cache.post_id].$comm_container()
			// return exports.cache.posts[exports.cache.post_id].$comm_box_datas || false;
		},

		/** 
		 * 从服务器获取评论列表 list.get_comm_data_from_server
		 */
		get_data_from_server : {
			/** 
			 * list.get_comm_data_from_server.ajax
			 */
			ajax : {
				/** 
				 * list.get_comm_data_from_server.ajax.init
				 */
				init : function(){
					$.ajax({
						url : exports.config.process_url,
						dataType : 'json',
						data : {
							'post-id' : exports.cache.post_id,
							'theme-nonce' : js_request['theme-nonce'],
							'type' : 'get-comments'
						}
					}).done(function(data){
						exports.list.get_data_from_server.ajax.done(data);
					}).fail(function(){
						exports.list.get_data_from_server.ajax.fail();
					}).always(function(){
						exports.list.get_data_from_server.ajax.always();
					});
				},
				done : function(data){
					if(data && data.status === 'success'){
						var comms = data.comments,
							comms_len = comms.length,
							parent_comms = {},
							child_comms = {},
							$child_comms = {},
							$comms = {},
							$parent_comms = {},
							$comments_list = $(exports.config.comm_box_id + exports.cache.post_id);
							
						/** 
						 * find all child & parent comments
						 */
						for(var i in comms){
							$comms[i] = $(exports.tpl.get_comm(comms[i]));
							/** find child */
							if(comms[i]['comment_parent']){
								if(!child_comms[comms[i]['comment_parent']]) child_comms[comms[i]['comment_parent']] = [];
								if(!$child_comms[comms[i]['comment_parent']]) $child_comms[comms[i]['comment_parent']] = [];
								child_comms[comms[i]['comment_parent']].push(comms[i]);
								$child_comms[comms[i]['comment_parent']].push($comms[i].addClass('children'));
							/** find parent */
							}else{
								parent_comms[i] = comms[i];
								$parent_comms[i] = $comms[i];
							}
						}

						var child_comm_append = function(parent_id,$child_comm){
							for(i in $child_comm){
								$comms[parent_id].append($child_comm[i]);
							}
						}
						/** clean comments */
						exports.cache.posts[exports.cache.post_id].$comm_container.children().remove();
						for(var i in comms){
							exports.cache.posts[exports.cache.post_id].$comm_container.append($comms[i]);
						}
						for(var i in child_comms){
							child_comm_append(i,$child_comms[i]);
						}
						/** set cache for comms by post id */
						exports.cache.posts[exports.cache.post_id].$comm_box_datas = exports.cache.posts[exports.cache.post_id].$comm_container;
						// console.log(exports.cache.posts[exports.cache.post_id]);
						
						/** 设置回复评论按钮的缓存 */
						exports.cache.posts[exports.cache.post_id].$reply_comm_btn = exports.cache.posts[exports.cache.post_id].$comm_container.find(exports.config.reply_comm_btn_id);
						// console.log(exports.cache.posts[exports.cache.post_id].$reply_comm_btn);
						
						/** 绑定点击 - 回复评论 */
						exports.cache.posts[exports.cache.post_id].$reply_comm_btn.off().on('click',function(){
							exports.respond.bind_core($(this));
							/** 取消默认行为 */
							return false;
						});

						/** 
						 * hide tip
						 */
						exports.list.tip('hide');
						/** location.hash */
						if(location.hash.indexOf('#comment-') === 0){
							var $scroll_to_comment = $(location.hash);
							if($scroll_to_comment[0]){
								exports.$scroll_ele.animate({
									scrollTop : $scroll_to_comment.offset().top - 20
								},300);
							}
						}

					}else if(data && data.status === 'error'){
						/** no_comment */
						if(data.id === 'no_comment'){
							exports.cache.posts[exports.cache.post_id].$comm_tip.html($('<div class="no-comment">' + exports.config.lang.M00004 + '</div>'));
						}else{
							exports.list.tip('info',data.msg);
						}
					}else{
						exports.list.tip('error',exports.config.lang.E00001);
					}
				},
				fail : function(){
					exports.list.tip('error',exports.config.lang.E00001);
				},
				always : function(){
				
				}
			}
		}
	};
	/** 
	 * 评论回复
	 */
	exports.respond = {
		init : function($bind_ele){
			/** 设置添加评论按钮的缓存 */
			exports.cache.posts[exports.cache.post_id].$add_comm_btn = $(exports.config.add_comm_btn_id + exports.cache.post_id);
			/** 绑定点击 - 添加评论 */
			exports.cache.posts[exports.cache.post_id].$add_comm_btn.off().on('click',function(){
				exports.respond.bind_core($(this));
				/** 取消默认行为 */
				return false;
			});
		},
		/** 
		 * respond.bind_core
		 */
		bind_core : function($this){
			/** 设置当前 post id 到缓存 */
			exports.cache.post_id = $this.data('postId');
			/** 父评论id */
			var comment_parent = $this.data('commentParent') ? $this.data('commentParent') : 0;
			/** 设置评论弹出层模板 */
			exports.cache.$respond = $(exports.tpl.get_respond({
				post_id : exports.cache.post_id,
				comment_parent : comment_parent
			}));
			/** 显示添加评论弹出层 */
			exports.common.dialog({
				id : 'respond',
				title : exports.config.lang.M00005,
				content : exports.cache.$respond,
				width : exports.get_dialog_width(),
				onshow: function () {
					exports.cache.$respond_content = exports.cache.$respond.find('#respond-content').focus();
				}
			});
			
			exports.respond.ajax.init();			
		},
		/** 
		 * respond.ajax
		 */
		ajax : {
			/** 
			 * respond.ajax.init 初始化
			 */
			init : function(){
				$.ajax({
					url : exports.config.process_url + '&' + $.param({
						'type' : 'get-respond',
						'post-id' : exports.cache.post_id,
						'theme-nonce' : js_request['theme-nonce']
					}),
					dataType : 'json'
				}).done(function(data){
					exports.respond.ajax.done(data);
				}).fail(function(){
					exports.respond.ajax.fail();
				}).always(function(){
					exports.respond.ajax.always();
				});
			},
			done : function(data){
				if(data && data.status === 'success'){
					/** 如果用户未登录 */
					if(!data.logged){
						/** set cache for $comment_author */
						exports.cache.$comment_author = exports.cache.$respond.find('#respond-name');
						/** set cache for $comment_author_email */
						exports.cache.$comment_author_email = exports.cache.$respond.find('#respond-email');
						/** set cache for $comment_author_url */
						exports.cache.$comment_author_url = exports.cache.$respond.find('#respond-url');
						
						/** set preset value */
						var commenter = data.commenter;
						commenter.comment_author && exports.cache.$comment_author.val(commenter.comment_author);
						
						commenter.comment_author_email && exports.cache.$comment_author_email.val(commenter.comment_author_email);
						
						commenter.comment_author_url && exports.cache.$comment_author_url.val(commenter.comment_author_url);
						
						/** show visitor container */
						exports.cache.$respond_visitor = exports.cache.$respond.find('#respond-visitor').show();
						/** set attr */
						if(data.require_name_email){
							exports.cache.$comment_author.attr({
								'required' : true,
								'placeholder' : exports.config.lang.M00007 + '*'
							});
							exports.cache.$comment_author_email.attr({
								'required' : true,
								'placeholder' : exports.config.lang.M00008 + '*'
							});
						}
						
					}
					/** show submit btn and bind */
					exports.cache.$respond.find('.form-group-submit').show();
					/** 
					 * validate
					 */
					exports.respond.validate.init();
					/** hide respond tip */
					
					exports.respond.tip('hide');
					
				}else if(data && data.status === 'error'){
				
				}
			},
			fail : function(data){
			
			},
			always : function(data){
			
			},
			
		},
		/** 
		 * 验证 respond.validate
		 */
		validate : {
			init : function(){
				exports.cache.$respond.validate({
					submitHandler : function() {
						exports.respond.tip('loading',exports.config.lang.M00001);
						/** 通过验证，进入数据提交 */
						exports.respond.validate.ajax.init();
						/** 取消默认行为 */
						return false;
					}
				});

			},
			/** 
			 * respond.validate.ajax
			 */
			ajax : {
				init : function(){
					$.ajax({
						url : exports.config.process_url + '&' + $.param({
							'post-id' : exports.cache.post_id,
							'theme-nonce' : js_request['theme-nonce'],
							'type' : 'post-comment'
						}),
						type : 'post',
						dataType : 'json',
						data : exports.cache.$respond.serialize()
					}).done(function(data){
						exports.respond.validate.ajax.done(data);
					}).fail(function(){
						exports.respond.validate.ajax.fail();
					}).always(function(){
					
					});				
				},
				/** 
				 * respond.validate.ajax.done
				 */
				done : function(data){
					if(data && data.status === 'success'){
						var tpl_data = data.comment;
						tpl_data.comment_class = ['new-comment'];
							
						exports.cache.$respond_comm = $(exports.tpl.get_comm(tpl_data)).hide();
						
						/** 如果是子评论，插入到父评论中 */
						if(tpl_data.comment_parent){
							exports.cache.posts[exports.cache.post_id].$comm_container.find('#comment-' + tpl_data.comment_parent).append(exports.cache.$respond_comm);
						/** 非子评论 */
						}else{
							/** append to comm container */
							exports.cache.posts[exports.cache.post_id].$comm_container.append(exports.cache.$respond_comm);
						
						}
						exports.cache.$respond_comm.slideDown();
						
						/** set success info */
						exports.respond.tip('success',exports.config.lang.M00002);
						exports.list.tip('hide');
						/** setTimeout to close */
						try{
							setTimeout(function(){
								exports.cache.dialog.close().remove();
							},3000);
						}catch(e){};				
					}else if(data && data.status === 'error'){
						exports.respond.tip('error',data.msg);
					}else{
						exports.respond.tip('error',exports.config.lang.E00001);
					}
				},
				/** 
				 * respond.validate.ajax.fail
				 */
				fail : function(){
					exports.respond.tip('error',exports.config.lang.E00001);
				},
				/** 
				 * respond.validate.ajax.always
				 */
				always : function(){
				
				}
			}
		},
		/**
		 * respond.tip
		 *
		 * @param string t The status type
		 * @param string s The status content
		 * @return null
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		tip : function(t,s){
			exports.cache.$respond_tip = exports.cache.$respond.find('#respond-tip');
			if(t === 'hide'){
				exports.cache.$respond_tip.hide();
			}else{
				if(t != 'error'){
					exports.cache.$respond.find('.form-group-submit').hide();
				}else{
					exports.cache.$respond.find('.form-group-submit').show();
				}
				exports.cache.$respond_tip.html(tools.status_tip(t,s)).show();
			}
		},
		
	};
	/** 
	 * reply
	 */
	exports.reply = {
	
	};
	/** 
	 * 评论翻页
	 */
	exports.paginav = {
	
	};
	
	
	exports.common = {
		/** 
		 * 弹出层
		 */
		dialog : function(args,action){
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
		}
	};
	/** 
	 * 模板
	 */
	exports.tpl = {
		/** 
		 * 单条评论模板
		 */
		get_comm : function(args){
			if(!args['comment_class']) args['comment_class'] = [];
			args['comment_class'].push('comment');
			var comm_class = args['comment_class'].join(' '),
				comm_id = args['comment_id'],
				comm_author_url = args['comment_author']['url'] ? args['comment_author']['url'] : 'javascript:void(0)',
				comm_author_name = args['comment_author']['name'],
				comm_author_gravatar = args['comment_author']['gravatar'],
				comm_content = args['comment_content'],
				comm_date = new Date(args['comment_date'] * 1000).toLocaleString(),
				comm_fri_date = args['comment_friendly_date'],
				comm_post_id = args['comment_post_id'],
				comm_author_name_str = comm_author_url ? '<a href="' + comm_author_url + '" target="_blank">' + comm_author_name + '</a>' : comm_author_name,
				tpl = 
'<div id="comment-' + comm_id + '" class="' + comm_class + '">'
+	'<article id="comment-body-' + comm_id + '" class="comment-body">'
+		'<header class="comment-area-img">'
+			'<a href="' + comm_author_url + '" class="comment-author-vcard" target="_blank">'
+				'<img src="' + comm_author_gravatar + '" alt="' + comm_author_name + '"/>'
+			'</a>'
+		'</header>'
+		'<div class="comment-area-tx">'
+			'<footer class="comment-metas">'
+				'<span class="comment-meta comment-author-name">' + comm_author_name_str + '</span>'
+				'<span class="comment-meta comment-time"><span class="icon-clock"></span><span class="after-icon">' + comm_fri_date + '</span></span>'
+				'<a href="javascript:;" class="comment-meta comment-reply reply" data-post-id="' + comm_post_id + '" data-comment-parent="' + comm_id + '"><span class="icon-undo"></span><span class="after-icon hide-on-mobile">' + exports.config.lang.M00010 + '</span></a>'
+			'</footer>'
+			'<div class="comment-content content-reset">' + comm_content + '</div>'
+		'</div>'
+	'</article>'
+'</div>';
			return tpl;
		},
		/** 
		 * 评论框模板
		 */
		get_respond : function(args){
			var comm_parent = args['comment_parent'],
				comm_post_id = args['post_id'];
				
				return ''+
'<form action="javascript:void(0)" id="respond">'
+	'<div class="form-group">'
+		'<textarea name="comment-content" class="form-control full-width" id="respond-content" rows="3" required  placeholder="' + exports.config.lang.M00005 + '*"></textarea>'
+	'</div>'
+	'<div class="hide" id="respond-visitor">'
+		'<div class="form-group clr">'
+		'<div class="respond-meta respond-meta-name">'
+			'<label for="respond-name" class="form-icon"><span class="icon-user"></span><span class="after-icon hide">' + exports.config.lang.M00008 + '</span></label>'
+			'<input type="text" name="comment-name" id="respond-name" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00007 + '" />'
+		'</div>'
+		'<div class="respond-meta respond-meta-email">' 
+			'<label for="respond-email" class="form-icon"><span class="icon-envelope"></span><span class="after-icon hide">' + exports.config.lang.M00008 + '</span></label>'
+			'<input type="email" name="comment-email" id="respond-email" class="form-control form-control-icon" placeholder="' + exports.config.lang.M00008 + '" />'
+		'</div>'
+		'</div>'
+		'<div class="form-group respond-meta-url">'
+			'<label for="respond-url" class="form-icon"><span class="icon-home"></span><span class="after-icon hide">' + exports.config.lang.M00012 + '</span></label>'
+			'<input type="url" name="comment-url" id="respond-url" class="form-control form-control-icon full-width" placeholder="' + exports.config.lang.M00012 + '" />'
+		'</div>'
+	'</div>'
+	'<div class="form-group form-group-submit hide">'
+		'<button type="submit" class="btn btn-primary submit"><span class="icon-paperplane"></span><span class="after-icon">' + exports.config.lang.M00006 + '</span></button>'
+		'<input type="hidden" name="comment-parent" id="comment-parent" value="' + comm_parent + '"/>'
+		'<input type="hidden" name="comment-post-id" id="comment-post-id" value="' + comm_post_id + '"/>'
+	'</div>'
+	'<div class="page-tip page-tip-bottom form-group" id="respond-tip">' + tools.status_tip('loading',exports.config.lang.M00001) + '</div>'
+'</form>';
		}
	};
	/** 
	 * 滚动元素
	 */
	exports.$scroll_ele = navigator.userAgent.toLowerCase().indexOf('webkit') === -1 ? $('html') : $('body');
	//get_dialog_width
	exports.get_dialog_width = function(){
		var bw = document.body.clientWidth;
		if(bw <= 767){
			return bw - 80;
		}else{
			return 400;
		}
	};

});