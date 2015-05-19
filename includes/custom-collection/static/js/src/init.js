define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');

	exports.config = {
		process_url : '',
		tpl : '',
		lang : {
			M01 : 'Loading, please wait...',
			M02 : 'A item has been deleted.',
			E01 : 'Sorry, server is busy now, can not respond your request, please try again later.'
		}
	}

	var cache = {},
		config = exports.config;
		
	exports.init = function(){
		list();
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

				$new_list.style.border = '2px solid #5cb85c';
				setTimeout(function(){
					$new_list.style.border = '';
				},500);

				bind_list($new_list);
				_cache.$container.appendChild($new_list);
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
			del();
			/** bind post id input blur action */
			show_post();
			
			/**
			 * delete action
			 */
			function del(){
				var helper = function(){
					$list.style.border = '2px solid #d9534f';
					setTimeout(function(){
						$list.parentNode.removeChild($list);
						//tools.ajax_loading_tip('success',config.lang.M02,3);
					},500);
					return false;
				};
				$list.querySelector('.clt-del').addEventListener('click', helper, false);;
			}

			/**
			 * get post data action
			 */
			function show_post(){
				post_id_blur();
				function post_id_blur(){
					var _posts_data = {},
						helper = function(){
							var $post_id = this,
								post_id = this.value;
							
						};
					$list.querySelector('.clt-post-id').addEventListener('blur',helper,false);
				}
			}
		}
	}


	function I(e){
		return document.getElementById(e);
	}
});