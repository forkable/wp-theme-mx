define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');

	exports.config = {
		process_url : '',
		tpl : '',
		min_posts : 5,
		max_posts : 10,
		lang : {
			M01 : 'Loading, please wait...',
			M02 : 'A item has been deleted.',
			M03 : 'Getting post data, please wait...',
			M04 : 'Previewing, please wait...',
			E01 : 'Sorry, server is busy now, can not respond your request, please try again later.',
			E02 : 'Sorry, the minimum number of posts is %d.',
			E03 : 'Sorry, the maximum number of posts is %d.',
			E04 : 'Sorry, the post id must be number, please correct it.'
		}
	}
	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		list();
		preview();
	}
	function get_posts_count(){
		return document.querySelectorAll('.clt-list').length;
	}
	function preview(){
		var $preview = I('clt-preview');
		cache.$preview_container = I('clt-preview-container');
		
		if(!$preview)
			return false;
			
		$preview.addEventListener('click', function(){
			var lists_count = get_posts_count();
			
			if(lists_count < config.min_posts){
				tools.ajax_loading_tip('error',config.lang.E02,3);
				return false;
			}else if(lists_count > config.max_posts){
				tools.ajax_loading_tip('error',config.lang.E03,3);
				return false;
			}

			show_preview();
			
			return false;
		}, false);
		
		function show_preview(){
			var $lists = document.querySelectorAll('.clt-list'),
				xhr = new XMLHttpRequest(),
				fd = new FormData();
			xhr.open('POST',config.process_url);
			/**
			 * loop lists
			 */
			for(var i = 0, len = $lists.length; i < len; i++){
				/**
				 * check empty input
				 */
				var $requires = $lists[i].querySelectorAll('[required]');
				/**
				 * loop requires
				 */
				for(var j = 0, l = $requires.length; j < l; j++){
					if($requires[j].value.trim() === ''){
						tools.ajax_loading_tip('error',$requires[j].getAttribute('title'),3);
						$requires[j].focus();
						return false;
					}
				}
				var id = $lists[i].getAttribute('data-id'),
					$imgs = $lists[i].querySelectorAll('img');
				/** title */
				fd.append('posts[' + id + '][title]',I('clt-post-title-' + id).value);
				/** content */
				fd.append('posts[' + id + '][content]',I('clt-post-content-' + id).value);
				/** post id */
				fd.append('posts[' + id + '][id]',I('clt-post-id-' + id).value);
				/** thumbnail */
				fd.append('posts[' + id + '][thumbnail]',$imgs[$imgs.length - 1].src);
			}
			/**
			 * show ajax tip
			 */
			tools.ajax_loading_tip('loading',config.lang.M04);
			
			fd.append('theme-nonce',js_request['theme-nonce']);
			fd.append('type','preview');
			xhr.send(fd);
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					preview_done(data);
				}else{
					preview_faild(xhr.responseText);
				}
			};
			xhr.onerror = function(){
				preview_faild(xhr.responseText);
			};
		}
		function preview_done(data){
			if(data && data.status === 'success'){
				cache.$preview_container.innerHTML = data.tpl;
				tools.ajax_loading_tip(data.status,data.msg,3);
				location.hash = '#' + cache.$preview_container.id;
			}else if(data && data.status === 'error'){
				tools.ajax_loading_tip(data.status,data.msg);
			}else{
				preview_faild();
			}
		}
		function preview_faild(data){
			tools.ajax_loading_tip('error',data);
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
				
				/** too many posts */
				if(get_posts_count() >= config.max_posts){
					tools.ajax_loading_tip('error',config.lang.E03,3);
					return false;
				}
				var rand = Date.now(),
					$tmp = document.createElement('div'),
					$new_list;
					
				$tmp.innerHTML = get_tpl(rand);
				$new_list = $tmp.firstChild;

				$new_list.classList.add('delete');
				_cache.$container.appendChild($new_list);
				/** bind list */
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
					
					/** not enough posts */
					if(get_posts_count() <= config.min_posts){
						tools.ajax_loading_tip('error',config.lang.E02,3);
						return false;
					}
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
					var $post_id = $list.querySelector('.clt-post-id'),
						helper = function(evt){
							evt.preventDefault();
							
							var post_id = this.value,
								placeholder = $list.getAttribute('data-id');
							if(post_id.trim() === '')
								return false;

							if(isNaN(post_id.trim()) === true){
								this.select();
								tools.ajax_loading_tip('error',config.lang.E04,3);
								return false;
							}
							/**
							 * if no exist cache, get data from server
							 */
							if(!get_post_cache_data(post_id)){
								ajax(post_id,placeholder,this);
							/**
							 * get post data from cache
							 */
							}else{
								set_post_data(post_id,placeholder);
							}
						}
					$post_id.addEventListener('change',helper,false);
					$post_id.addEventListener('blur',helper,false);
				}
				function ajax(post_id,placeholder,$post_id){
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
							/** focus post id */
							$post_id.select();
							/** tip */
							tools.ajax_loading_tip(data.status,data.msg,3);
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
						$thumbnail = I('clt-thumbnail-preview-container-' + placeholder).querySelector('img');
					if(cache.posts[post_id].title)
						$title.value = cache.posts[post_id].title;

					if(cache.posts[post_id].excerpt && $content.value.trim() === '')
						$content.value = cache.posts[post_id].excerpt;
						
					if(cache.posts[post_id].thumbnail)
						$thumbnail.src = cache.posts[post_id].thumbnail.url;
				}
			}
		}
	}

	function upload(){
		var $file = I('clt-file'),
			$progress_bar = I('clt-file-progress'),
			$complete = I('clt-file-completion'),
			$files = I('clt-files'),
			$cover = I('clt-cover'),
			_cache = {};
		_cache.$progress = I('clt-file-progress');
		_cache.$tip = I('clt-file-tip');

		if(!$file)
			return false;

		$file.addEventListener('change',file_select,false);
		$file.addEventListener('drop',file_select,false);

		function file_select(e){
			e.stopPropagation();
			e.preventDefault();
			_cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			file_upload(_cache.files[0]);
		}
		function file_upload(file){
			var	reader = new FileReader();
			reader.onload = function (e) {
				submission(file);
			};
			reader.readAsDataURL(file);
		}
		function submission(file){

			/** loading tip */
			$file_progress_bar.style.width = '10%';
			progress_tip('loading',config.lang.M01);
			
			var fd = new FormData(),
				xhr = new XMLHttpRequest();

			fd.append('type','add-cover');
			fd.append('theme-nonce',js_request['theme-nonce']);
			fd.append('img',file);
			xhr.open('post',config.process_url);
			xhr.send(fd);
			xhr.upload.onprogress = function(e){
				if (e.lengthComputable) {
					var percent = e.loaded / e.total * 100;		
					$file_progress_bar.style.width = percent + '%';
				}
			};
			xhr.onload = function(){
				if (xhr.status >= 200 && xhr.status < 400) {
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(err){data = xhr.responseText}
					if(data && data.status === 'success'){
						$cover.src = data.url;
						tools.ajax_loading_tip(data.status,data.msg);
					}else if(data && data.status === 'error'){
						tools.ajax_loading_tip(data.status,data.msg,3);
					}else{
						tools.ajax_loading_tip(data.status,data);
					}
				}else{
					tools.ajax_loading_tip('error',config.lang.E01);
				}
				progress_tip('hide');
			};
			xhr.onerror = function(){
				tools.ajax_loading_tip('error',config.lang.E01);
			};
		}
		function progress_tip(t,s){
			if(t === 'hide'){
				_cache.$progress.style.display = 'none';
				return false;
			}
			_cache.$progress_tx.innerHTML = tools.status_tip(t,s);
				
		}
	}
	function I(e){
		return document.getElementById(e);
	}
});