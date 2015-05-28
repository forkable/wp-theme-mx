/**
 * theme-comment-ajax
 *
 * @version 3.0.0
 * @author INN STUDIO <inn-studio.com>
 */
define(function(require, exports, module){
	'use strict';

	var js_request 	= require('theme-cache-request'),
		tools 		= require('modules/tools');


	exports.config = {
		iden : 'theme_comment_ajax',
		process_url : '',
		pagi_process_url : '',
		post_id : '',
		lang : {
			M00001 : 'Loading, please wait...', 
			M00002 : 'Commented successfully, thank you!',
			M00003 : 'Previous',
			M00004 : 'Next',
			M00005 : '{n} page',
			E00001 : 'Sorry, some server error occurred, the operation can not be completed, please try again later.'
		}
		
	
	};
	var cache = {},
		config = exports.config;
	
	exports.init = function(){
		tools.ready(function(){
			/**
			 * set comment count
			 */
			exports.count.set();
			
			cache.$comment_list_container = I('comment-list-' + config.post_id);
			cache.$comments = I('comments');
			if(!cache.$comment_list_container)
				return false;
			
			
			window.addComment = addComment;

			
			exports.list.init();
			/**
			 * pagination
			 */
			var pagi = new exports.pagination();

			pagi.lang.loading = config.lang.M00001;
			pagi.lang.error = config.lang.E00001;
			pagi.lang.prev = config.lang.M00003;
			pagi.lang.next = config.lang.M00004;
			pagi.lang.midd = config.lang.M00005;
			
			pagi.pages = js_request[config.iden].pages;
			pagi.cpage = js_request[config.iden].cpage;
			pagi.url_format = config.pagi_process_url;
			pagi.init();

			/**
			 * respond
			 */
			var rsp = new exports.respond();
			rsp.init();
		});
	};
	exports.count = {
		set : function(n){
			cache.$count = I('comment-number-' + config.post_id);
			if(!cache.$count)
				return false;

			cache.$count.innerHTML = n ? n : js_request[config.iden].count;
		}
	};
	exports.list = {
		init : function(){
			var _list = this;
			//console.log(js_request[config.iden].comments);
			if(!js_request[config.iden].comments)
				return false;
			cache.$comment_list_container.innerHTML = js_request[config.iden].comments;
		},
		get : function(){
			var _list = this,
				xhr = new XMLHttpRequest(),
				param = tools.param({
					'type' : 'get-comments',
					'post-id' : config.post_id,
					'theme-nonce' : js_request['theme-nonce']
				});
			xhr.open('GET',config.pagi_process_url + '&' + param);
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText);}catch(e){data = xhr.responseText}
					if(data && data.status){
						_list.done(data);
					}else{
						_list.fail(data);
					}
					_list.always(data);
				}
			}
			
		},
		done : function(data){
			if(data.status === 'success'){
				cache.$comment_list_container.innerHTML = data.comments;
				
			}
		},
		faild : function(tx){
			
		},
		always : function(data){
			
		}
	};

	exports.pagination = function(){
		this.id = 'comment-pagination';
		this.container_id = 'comment-pagination-container';
		this.cpage = 1;
		this.pages = 1;
		this.class_name = 'comment-pagination';

		this.url_format = '';/** http://xxx.com/pages=n */
		this.lang = {
			loading : 'Loading, please wait...',
			error : 'Sorry, some server error occurred, the operation can not be completed, please try again later.',
			prev : 'Previous',
			next : 'Next',
			midd : '{n} page'
		};

		this.before = function(){};
		this.done = function(){};
		this.faild = function(){};
		this.always = function(){};

		var _cache = {},
			_that = this,
			target_page;
		
		this.init = function(){
			_cache.$container = document.getElementById(_that.container_id);
			if(!_cache.$container)
				return false;

			_cache.$container.appendChild(create());
			//console.log(_that.cpage);
			set_cache(_that.cpage,cache.$comment_list_container.innerHTML);
		};
		function set_cache(cpage,comments){
			//console.log('set ' +cpage);
			if(!_cache.comments)
				_cache.comments = [];
			/**
			 * for cache imgs
			 */
			var $tmp = tools.parseHTML(comments),
				imgs = [];
			for(var i = 0, len = $tmp.length; i < len; i++){
				Array.prototype.forEach.call($tmp[i].querySelectorAll('img'),function(el,i){
					imgs[i] = new Image();
					imgs[i].src = el.src;
				});
			}
			
			_cache.comments[cpage] = comments;
		}
		function get_cache(cpage){
			//console.log('get ' +cpage);
			return !_cache.comments || !_cache.comments[cpage] ? false : _cache.comments[cpage];
		}
		function create(){
			if(_that.pages <= 1)
				return false;
				
			_cache.$pagi = document.createElement('div');
			_cache.$pagi.id = _that.id;
			_cache.$pagi.setAttribute('class','comment-pagination btn-group btn-group-sm');

			_cache.$pagi.appendChild(create_prev());
			_cache.$pagi.appendChild(create_next());

			return _cache.$pagi;
		}
		function create_prev(){
			var prev_class = _that.cpage <= 1 ? 'disabled' : '',
				attrs = {
					'class' : 'prev btn btn-success ' + prev_class,
					'href' : 'javascript:;'
				};
			_cache.$prev = document.createElement('a');
			for(var k in attrs){
				_cache.$prev.setAttribute(k,attrs[k]);
			}
			_cache.$prev.innerHTML = _that.lang.prev;
			_cache.$prev.addEventListener('click',prev_click,false);
			return _cache.$prev;
		}
		/**
		 * Previous btn click
		 */
		function prev_click(){
			if(_that.cpage <= 1)
				return false;
			target_page = parseInt(_that.cpage) - 1;
			ajax();
		}
		function done_prev(){
			if(_that.cpage <= 1){
				_cache.$prev.classList.add('disabled');
			}else{
				_cache.$prev.classList.remove('disabled');
			}
		}
		function create_next(){
			var next_class = _that.cpage > _that.pages - 1 ? 'disabled' : '',
				attrs = {
					'class' : 'next btn btn-success ' + next_class,
					'href' : 'javascript:;'
				};
			_cache.$next = document.createElement('a');
			for(var k in attrs){
				_cache.$next.setAttribute(k,attrs[k]);
			}
			_cache.$next.innerHTML = _that.lang.next;
			//console.log(_that.cpage == _that.pages);
			_cache.$next.addEventListener('click',next_click,false);
			return _cache.$next;
		}
		/**
		 * Next btn click
		 */
		function next_click(){
			//console.log(_that.cpage == _that.pages);
			if(_that.cpage == _that.pages)
				return false;
				
			target_page = parseInt(_that.cpage) + 1;
			ajax();
		}
		function done_next(){
			if(_that.cpage == _that.pages){
				_cache.$next.classList.add('disabled');
			}else{
				_cache.$next.classList.remove('disabled');
			}
		}
		function get_process_url(){
			return _that.url_format.replace('=n','=' + target_page);
		}
		
		function ajax(){
			scroll_to_list();
			/**
			 * restore form
			 */
			if(cache.$comments.querySelector('#respond'))
				addComment.cancelMove();

			/** set cpage */
			set_cpage();
			/**
			 * check cache
			 */
			if(get_cache(_that.cpage)){
				/**
				 * set html
				 */
				cache.$comment_list_container.innerHTML = get_cache(_that.cpage);
				
				done_prev();
				done_next();
				return false;
			}
			
			/** loading tip */
			tools.ajax_loading_tip('loading',_that.lang.loading);
			
			var xhr = new XMLHttpRequest();
			xhr.open('GET',get_process_url() + '&theme-nonce=' + js_request['theme-nonce']);
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					if(data && data.status === 'success'){
						
						/**
						 * set html
						 */
						cache.$comment_list_container.innerHTML = data.comments;
						/** set cpage */
						//set_cpage();
						/**
						 * cache
						 */
						set_cache(_that.cpage,data.comments);
						
						
						/** close tip */
						tools.ajax_loading_tip('hide');
						done_next();
						done_prev();
					}else if(data && data.status === 'error'){
						tools.ajax_loading_tip(data.status,data.msg);
					}else{
						tools.ajax_loading_tip('error',data);
					}
					//console.log(_that.cpage);
				}
				_that.always();
				xhr = null;
			};
			xhr.onerror = function(){
				tools.ajax_loading_tip('error',_that.lang.error);
			};
		}
		function set_cpage(){
			if(_that.cpage >= target_page){
				_that.cpage--;
			}else{
				_that.cpage++;
			}
		}
		/** scroll to comment list container offset top */
		function scroll_to_list(){
			location.hash = 'comments';
			//window.scrollTo(0,cache.$comment_list_container.offsetTop);
			//var y = cache.$comment_list_container.getBoundingClientRect().top + document.documentElement.scrollTop;
			//window.scrollTo(0,y - 40);
		}
	}


	/**
	 * respond
	 */
	exports.respond = function(){
	
		var _cache = {},
			_config = {
				logged : js_request[config.iden].logged,
				registration : js_request[config.iden].registration,
				prefix_comment_body_id : 'comment-body-'
			},
			_that = this;

		this.init = function(){
			goto_click();
			fm_init();
		};


		function fm_init(){
			_cache.$respond 		= I('respond');
			_cache.$fm 				= I('commentform');
			_cache.$must_logged 	= I('respond-must-login');
			_cache.$loading_ready 	= I('respond-loading-ready');
			_cache.$avatar 			= I('respond-avatar');
			_cache.$area_visitor 	= I('area-respond-visitor');
			_cache.$comment_parent 	= I('comment_parent');
			_cache.$comment_ta 		= I('comment-form-comment');
			_cache.$submit_btn		= _cache.$fm.querySelector('.submit');
			
			
			if(!_cache.$respond || !_cache.$fm)
				return false;

			/**
			 * hide loading ready
			 */
			if(_cache.$loading_ready)
				_cache.$loading_ready.parentNode.removeChild(_cache.$loading_ready);
				
			/**
			 * if not logged and need registration, return false
			 */
			if(_config.registration && !_config.logged){
				_cache.$must_logged.style.display = 'block';
				return false;
			}
			/**
			 * ctrl + enter to submit
			 */
			if(_cache.$comment_ta){
				
				_cache.$comment_ta.addEventListener('keydown',function(e){
					if (e.keyCode == 13 && e.ctrlKey) {
						_cache.$submit_btn.click();
						return false;
					}
				},false);
			}
			/**
			 * user logged
			 */
			if(_config.logged){
				if(_cache.$avatar)
					_cache.$avatar.src = js_request[config.iden]['avatar-url'];

				if(_cache.$area_visitor)
					_cache.$area_visitor.parentNode.removeChild(_cache.$area_visitor);
			}else{
				
			}
			_cache.$fm.style.display = 'block';

			_cache.$fm.addEventListener('submit',fm_submit,false);
		}
		function fm_submit(e){
			/**
			 * check comment textarea
			 */
			if(_cache.$comment_ta.value.trim() === ''){
				_cache.$comment_ta.focus();
				tools.ajax_loading_tip('error',_cache.$comment_ta.getAttribute('title'),3);
				return false;
			}
			/**
			 * check requred input and format
			 */
			if(!_config.logged && _config.registration){
				var $inputs = _cache.$fm.querySelectorAll('input[required]');
				for(var i = 0, len = $inputs.length; i < len; i++){
					/** check email */
					if($inputs[i].getAttribute('type') === 'email' && !tools.is_email($inputs[i].value)){
						tools.ajax_loading_tip('error',$inputs[i].getAttribute('title'),3);
						return false;
					}
					/** check null value */
					if($inputs[i].value.trim() === ''){
						tools.ajax_loading_tip('error',$inputs[i].getAttribute('title'),3);
						return false;
					}
				}
			}

			/**
			 * ajax send
			 */
			tools.ajax_loading_tip('loading',config.lang.M00001);
			_cache.$submit_btn.setAttribute('disabled',true);
			ajax();
			return false;
		}
		function ajax(){
			var xhr = new XMLHttpRequest(),
				fd = new FormData(_cache.$fm);
			fd.append('theme-nonce',js_request['theme-nonce']);
			xhr.open('POST',config.process_url);
			xhr.send(fd);
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText);}catch(e){data = xhr.responseText}
					if(data && data.status === 'success'){
						/** do not use srcset */
						data.comment = data.comment.replace('srcset','data-srcset');
						var $new_comment = tools.parseHTML(data.comment)[0];
						$new_comment.classList.add('new');
						/**
						 * if children respond
						 */
						if(_cache.$comment_parent.value != 0){
							var $parent_comment_body = I(_config.prefix_comment_body_id + _cache.$comment_parent.value);
							$parent_comment_body.insertAdjacentHTML('afterend','<ul class="children">' + $new_comment.innerHTML + '</ul>');
							/** restore respond */
							addComment.cancelMove();
						}else{
							cache.$comment_list_container.appendChild($new_comment);
						}
						/** clear comment textarea */
						_cache.$comment_ta.value = '';
						
						/** hide comment loading */
						var $comment_loading = cache.$comments.querySelector('.comment-loading');
						if($comment_loading)
							$comment_loading.parentNode.removeChild($comment_loading);
						/** set comment number */
						var $badge = I('comment-number-' + data.post_id);
						if($badge){
							if(isNaN($badge.innerHTML)){
								$badge.innerHTML = 1;
							}else{
								$badge.innerHTML++;
							}
						}
						/**
						 * show $comments
						 */
						cache.$comments.style.display = 'block';
						
						/** show success tip */
						tools.ajax_loading_tip(data.status,data.msg,3);
					}else if(data && data.status === 'error'){
						tools.ajax_loading_tip(data.status,data.msg);
					}else{
						tools.ajax_loading_tip('error',config.lang.E00001);
					}
				}else{
					tools.ajax_loading_tip('error',config.lang.E00001);
				}
				/** enable submit btn */
				_cache.$submit_btn.removeAttribute('disabled');
			};
			xhr.onerror = function(){
				tools.ajax_loading_tip('error',config.lang.E00001);
				_cache.$submit_btn.removeAttribute('disabled');
			}
			
		}
		function goto_click(){
			_cache.$goto =I('goto-comment');
			_cache.$comment = I('comment-form-comment');
			if(!_cache.$goto || !_cache.$comment)
				return false;
				
			_cache.$goto.style.display = 'block';
			_cache.$goto.onclick = function(){
				setTimeout(function(){
					_cache.$comment.focus();
				},300);
			}
		}
		
	}

	
	/**
	 * form comment-reply.js
	 */
	var addComment = {
		cache : {},
		cancelMove : function(){
			var t = this;

			t.cache.$parent.value = '0';
			t.cache.$tmp.parentNode.insertBefore(t.cache.$respond, t.cache.$tmp);
			//t.cache.$tmp.parentNode.removeChild(temp);
			
			t.cache.$cancel.style.display = 'none';
			t.cache.$cancel.onclick = null;
		},
		moveForm : function(commId, parentId, respondId, postId) {
			var t = this;

			t.cache.$comm 		= I(commId);
			t.cache.$respond 	= I(respondId);
			t.cache.$cancel 	= I('cancel-comment-reply-link');
			t.cache.$parent 	= I('comment_parent');
			t.cache.$post 		= I('comment_post_ID'),
			t.cache.$comment 	= I('comment-form-comment');

			if ( ! t.cache.$comm || ! t.cache.$respond || ! t.cache.$cancel || ! t.cache.$parent )
				return;

			postId = postId || false;

			if ( ! t.cache.$tmp ) {
				t.cache.$tmp = document.createElement('div');
				t.cache.$tmp.id = 'wp-temp-form-div';
				t.cache.$tmp.style.display = 'none';
				t.cache.$respond.parentNode.insertBefore(t.cache.$tmp, t.cache.$respond);
			}

			t.cache.$comm.parentNode.insertBefore(t.cache.$respond, t.cache.$comm.nextSibling);
			if ( t.cache.$post && postId )
				t.cache.$post.value = postId;
			t.cache.$parent.value = parentId;
			t.cache.$cancel.style.display = 'block';

			t.cache.$cancel.onclick = function() {
				t.cancelMove();
				return false;
			};

			if(t.cache.$comment)
				t.cache.$comment.focus();

			return false;
		}
	};

	function I(e){
		return document.getElementById(e);
	}
	
});