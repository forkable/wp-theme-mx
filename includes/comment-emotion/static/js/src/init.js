define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools');
	exports.config = {
		
	}

	exports.init = function(){
		tools.ready(exports.bind);
	}

	var cache = {},
		config = exports.config;
		
	exports.bind = function(){
		cache.$emotion_btns = document.querySelectorAll('.comment-emotion-pop-btn');
		cache.$pop = document.querySelectorAll('.comment-emotion-area-pop .pop');
		cache.$comment = I('comment-form-comment');
		cache.$emotion_faces = document.querySelectorAll('.comment-emotion-area-pop a');
		if(!cache.$emotion_btns || !cache.$emotion_btns[0])
			return;
			
		cache.pop_hide = [];
		cache.replaced = [];
		pop();
		insert();
	}
	function insert(){
		function insert_content(){
			cache.$comment.focus();
			var caret_pos = cache.$comment.selectionStart,
				old_val = cache.$comment.value;
			cache.$pop[cache.active_pop_i].style.display = 'none';
			
			cache.$comment.value = old_val.substring(0,caret_pos) + ' ' + this.getAttribute('data-content') + ' ' + old_val.substring(caret_pos);

			cache.pop_hide[cache.active_pop_i] = true;
			cache.$pop[cache.active_pop_i].style.display = 'none';

		}
		for( var i = 0, len = cache.$emotion_faces.length; i < len; i++){
			cache.$emotion_faces[i].addEventListener('click',insert_content);
		}
	}
	function pop(){
		function hide_pop(e){
			e.preventDefault();
			if(cache.active_pop_i !== false){
				cache.$pop[cache.active_pop_i].style.display = 'none';
				cache.$comment.focus();
			}
				
		}
		function show_pop(){
			/**
			 * hide other pop
			 */
			for( var i = 0, len = cache.$pop.length; i < len; i++){
				if(cache.pop_hide[i] !== true){
					cache.$pop[i].style.display = 'none';
					cache.pop_hide[i] = true;
				}
				if(this == cache.$emotion_btns[i]){
					console.log(i);
					cache.active_pop_i = i;
					cache.pop_hide[i] = false;
					cache.$pop[i].style.display = 'block';
					
				}
			}
			/** replace data-url to src attribute */
			if(!cache.replaced[cache.active_pop_i]){
				cache.replaced[cache.active_pop_i] = true;
				var $imgs = cache.$pop[cache.active_pop_i].querySelectorAll('img');
				for(var i = 0, len = $imgs.length; i < len; i++){
					$imgs[i].src = $imgs[i].getAttribute('data-url');
					$imgs[i].removeAttribute('data-url');
				}
			}
		}
		for(var i = 0, len = cache.$emotion_btns.length; i < len; i++){
			cache.$emotion_btns[i].addEventListener('click', show_pop);
		}
		/**
		 * close btn
		 */
		cache.$closes = document.querySelectorAll('.comment-emotion-close');
		for(var i = 0, len = cache.$closes.length; i < len; i++){
			cache.$closes[i].addEventListener('click', hide_pop);
		}
	}
	function I(e){
		return document.getElementById(e);
	}
});