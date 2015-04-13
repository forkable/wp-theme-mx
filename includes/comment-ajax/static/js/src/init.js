/**
 * theme-comment-ajax
 *
 * @version 3.0.0
 * @author KM@INN STUDIO
 */
define(function(require, exports, module){
	'use strict';

	var $ 			= require('modules/jquery'),
		jQuery		= $,
		js_request 	= require('theme-cache-request'),
		tools 		= require('modules/tools');
					require('modules/jquery.validate');
					require('modules/jquery.validate.lang.{locale}');
	

	
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
		}
		
	
	};
	var cache = {},
		config = exports.config;
	
	exports.init = function(){
		//alert('a');
		tools.ready(function(){
			window.addComment = addComment;
			exports.list.get();
		});
	};

	exports.list = {
		get : function(){
			var xhr = new XMLHttpRequest(),
				param = tools.param({
					'type' : 'get-comments',
					'post-id' : config.post_id
				});
			xhr.open('GET',config.process_url + '&' + param);
			xhr.send();
			//xhr.onload = function(){
				
			//}
		}
	};
	/**
	 * form comment-reply.js
	 */
	var addComment = {
		moveForm : function(commId, parentId, respondId, postId) {
			var t = this, div, comm = t.I(commId), respond = t.I(respondId), cancel = t.I('cancel-comment-reply-link'), parent = t.I('comment_parent'), post = t.I('comment_post_ID');

			if ( ! comm || ! respond || ! cancel || ! parent )
				return;

			t.respondId = respondId;
			postId = postId || false;

			if ( ! t.I('wp-temp-form-div') ) {
				div = document.createElement('div');
				div.id = 'wp-temp-form-div';
				div.style.display = 'none';
				respond.parentNode.insertBefore(div, respond);
			}

			comm.parentNode.insertBefore(respond, comm.nextSibling);
			if ( post && postId )
				post.value = postId;
			parent.value = parentId;
			cancel.style.display = 'block';

			cancel.onclick = function() {
				var t = addComment, temp = t.I('wp-temp-form-div'), respond = t.I(t.respondId);

				if ( ! temp || ! respond )
					return;

				t.I('comment_parent').value = '0';
				temp.parentNode.insertBefore(respond, temp);
				temp.parentNode.removeChild(temp);
				this.style.display = 'none';
				this.onclick = null;
				return false;
			};

			try { t.I('comment').focus(); }
			catch(e) {}

			return false;
		},
		cache : [],
		I : function(e) {
			//if(cache[e])
				//return cache[e];
			return document.getElementById(e);
			//cache[e] = document.getElementById(e);
			//console.log(cache[e]);
			//return cache[e];
		}
	};
	
	
});