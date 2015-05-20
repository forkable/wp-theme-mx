define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');

	exports.config = {
		process_url : '',
		tpl : '',
		max_posts : 10,
		min_posts : 5,
		lang : {
			M01 : 'Loading, please wait...',
			M02 : 'A item has been deleted.',
			M03 : 'Getting post data, please wait...',
			M05 : 'Sorry, the minimum number of posts is %d',
			M06 : 'Sorry, the maximum number of posts is %d',
			E01 : 'Sorry, server is busy now, can not respond your request, please try again later.'
		}
	}

	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		list();
		preview();
	}
	/**
	 * check the post number
	 * @return mixed -1|< , 0|> , true|pass
	 */
	function check_count(){
		var $posts = document.querySelectorAll('.clt-list');
	
		if(!$posts || $posts.length < config.min_posts)
			return -1;

		if($posts.length > config.max_posts)
			return 0;

		return true;
	}
	function preview(){
		var $preview = I('clt-preview');
		if(!$preview)
			return false;
		function action_click(){
			
		}
		$preview.addEventListener('click', function(){
			var check = check_count();
			
			if(check  === -1){
				tools.ajax_loading_tip('error',config.lang.M05);
			}else if(check === 0){
				tools.ajax_loading_tip('error',config.lang.M06);
			}

			show_preview();
			
			return false;
		}, false);
		
		function show_preview(){
			var $lists = document.querySelectorAll('.clt-list');
			for(var i = 0, len = $lists.length; i < len; i++){
				$lists[i].
			}
		}
		function get_tpl($list){
			var $imgs = $list.querySelectorAll('img'),
				img = '';
			if($imgs.length > 0){
				/** get the last img */
				var $img = $imgs[$imgs.length - 1],
					$tmp = document.createElement('div');
				$tmp.appendChild($img);
				img = $tmp.innerHTML;
			}
			
			return '<div class="collection-list row">' +
	'<a class="col-xs-12 col-sm-5 col-md-3 col-lg-2">' +
		img
	'</a>' + 
'</div>';
		}
	}
	function list(){
		var _cache = {},
			$lists = document.querySelectorAll('.clt-list');
		if(!$lists[0])
			return false;
			
		_cache.$add = I('clt-add-post');
		_cache.$container = I('clt-posts-container');
		
			
		/**
		 * bind the lsits
		 */
		for(var i = 0, len = $lists.length; i < len; i++){
			bind_list($lists[i]);
		}
		/**
		 * bind the add list btn
		 */
		add_list();
		/**
		 * action add new psot
		 */
		function add_list(){
			var helper = function(){
				var rand = Date.now(),
					$tmp = document.createElement('div'),
					$new_list;
					
				$tmp.innerHTML = get_tpl(rand);
				$new_list = $tmp.firstChild;

				$new_list.classList.add('delete');
				_cache.$container.appendChild($new_list);
				bind_list($new_list);
				
				setTimeout(function(){
					$new_list.classList.remove('delete');
				},1);

				return false;
			};
			_cache.$add.addEventListener('click', helper, false);
		}
		function get_tpl(placeholder){
			return config.tpl.replace(/%placeholder%/g,placeholder);
		}
		/**
		 * bind list
		 */
		function bind_list($list){
			if(!$list)
				return false;
			
			/** bind delete action */
			del($list);
			/** bind post id input blur action */
			show_post($list);
			
			/**
			 * delete action
			 */
			function del($list){
				var helper = function(){
					$list.classList.add('delete');
					setTimeout(function(){
						$list.parentNode.removeChild($list);
					},500);
					return false;
				};
				$list.querySelector('.clt-del').addEventListener('click', helper, false);;
			}

			/**
			 * get post data action
			 */
			function show_post($list){
				post_id_blur();
				function post_id_blur(){
					var helper = function(){
						var post_id = this.value,
							placeholder = $list.getAttribute('data-id');
						if(post_id.trim() === '')
							return false;
						/**
						 * if no exist cache, get data from server
						 */
						if(!get_post_cache_data(post_id)){
							ajax(post_id,placeholder);
						/**
						 * get post data from cache
						 */
						}else{
							set_post_data(post_id,placeholder);
						}
					};
					$list.querySelector('.clt-post-id').addEventListener('change',helper,false);
				}
				function ajax(post_id,placeholder){
					/**
					 * loading tip
					 */
					tools.ajax_loading_tip('loading',config.lang.M03);
					
					var xhr = new XMLHttpRequest(),
						ajax_data = {
							'type' : 'get-post',
							'post-id' : post_id,
							'theme-nonce' : js_request['theme-nonce']
						};
					xhr.open('GET',config.process_url + '&' + tools.param(ajax_data));
					xhr.send();
					xhr.onload = function(){
						if(xhr.status >= 200 && xhr.status < 400){
							var data;
							try{data = JSON.parse(xhr.responseText)}catch(err){data = xhr.responseText}
							done(data);
						}else{
							tools.ajax_loading_tip('error',config.lang.E01);
						}
					};
					xhr.onerror = function(){
						tools.ajax_loading_tip('error',config.lang.E01);
					};
					function done(data){
						if(data && data.status === 'success'){
							/** set cache */
							set_post_cache(post_id,data);
							/** set to html */
							set_post_data(post_id,placeholder);
							/** tip */
							tools.ajax_loading_tip(data.status,data.msg,3);
						}else if(data && data.status === 'error'){
							/** set cache */
							set_post_cache(post_id,data);
							/** tip */
							tools.ajax_loading_tip(data.status,data.msg);
						}else{
							tools.ajax_loading_tip('error',data);
						}
					}
				}
				/**
				 * set post data to cache
				 */
				function set_post_cache(post_id,data){
					if(cache.posts && cache.posts[post_id])
						return false;
						
					if(!cache.posts)
						cache.posts = {};

					cache.posts[post_id] = {
						'thumbnail' : data.thumbnail,
						'title' : data.title,
						'excerpt' : data.excerpt
					};
				}
				function get_post_cache_data(post_id,key){
					if(!cache.posts || !cache.posts[post_id])
						return false;
						
					if(!key)
						return cache.posts[post_id];

					return cache.posts[post_id][key];
						
				}
				/**
				 * set post data to html
				 */
				function set_post_data(post_id,placeholder){
					var $title = I('clt-post-title-' + placeholder),
						$content = I('clt-post-content-' + placeholder),
						$preview_container = I('clt-preview-container-' + placeholder);
					if(cache.posts[post_id].title)
						$title.value = cache.posts[post_id].title;

					if(cache.posts[post_id].excerpt && $content.value.trim() === '')
						$content.value = cache.posts[post_id].excerpt;

					if(cache.posts[post_id].thumbnail)
						$preview_container.innerHTML = '<img src="' + cache.posts[post_id].thumbnail.url + '" alt="' + cache.posts[post_id].title + '" width="' + cache.posts[post_id].thumbnail.size[0] + '" height="' + cache.posts[post_id].thumbnail.size[1] + '" class="clt-preview">';
				}
			}
		}
	}


	function I(e){
		return document.getElementById(e);
	}
});