define(function(require, exports, module){
	'use strict';

	require.async(['modules/lazyload','modules/bootstrap-without-jq'],function(_a,_b){});
	
	var tools = require('modules/tools');
	
	
	exports.config = {
		is_home : false
	
	};
	exports.init = function(){
		tools.ready(function(){
			exports.hide_no_js();
			exports.search();
			exports.page_nagi();

		});
	}
	exports.page_nagi = function(){
		var $post = document.querySelector('.singluar-post'),
			$nagi = document.querySelector('.page-pagination'),
			post_top,
			max_bottom,
			is_hide = false;

		if(!$post || !$nagi)
			return;
		reset_nagi_style();
		
		window.addEventListener('resize',function(){
			reset_nagi_style()
		});
		
		$nagi.style.display = 'block';
		//window.addEventListener('scroll',function(){
		//	if(this.pageYOffset > post_bottom){
		//		if(!is_hide){
		//			$nagi.style.display = 'none';
		//			is_hide = true;
		//		}
		//	}else{
		//		if(is_hide){
		//			$nagi.style.display = 'block';
		//			is_hide = false;
		//		}
		//	}
		//});
		function reset_nagi_style(){
			post_top = getElementTop($post);
			max_bottom = post_top + $post.querySelector('.panel-body').clientHeight;
			$nagi.style.left = getElementLeft($post) + 'px';
			$nagi.style.width = $post.clientWidth + 'px';
		}
		function getElementLeft(e){
			var l = e.offsetLeft,
				c = e.offsetParent;
			while (c !== null){
				l += c.offsetLeft;
				c = c.offsetParent;
			}
			return l;
		}
		function getElementTop(e){
			var l = e.offsetTop,
				c = e.offsetParent;
			while (c !== null){
				l += c.offsetTop;
				c = c.offsetParent;
			}
			return l;
		}
		console.log(getElementLeft($post));
}
	exports.search = function(){
		var Q = function(s){
				return document.querySelector(s);
			},
			$btn = Q('.main-nav a.search');
			
		if(!$btn)
			return false;
			
		var $fm = Q($btn.getAttribute('data-target')),
			$input = $fm.querySelector('input[type="search"]'),
			submit_helper = function(){
				if($input.value.trim() === '')
					return false;
			};
			
		$btn.addEventListener('click',function(){
			setTimeout(function(){
				$input.focus();
			},100);
		},false);

		$fm.onsubmit = submit_helper;
	}
	exports.hide_no_js = function(){
		var A = function(e){
				return document.querySelectorAll(e);
			},
			$no_js = A('.hide-no-js'),
			$on_js = A('.hide-on-js');
		if($no_js[0]){
			Array.prototype.forEach.call($no_js, function(el){
				el.style.display = 'none';
			});
		}
		if($on_js[0]){
			Array.prototype.forEach.call($on_js, function(el){
				el.style.display = 'block';
			});
		}
	};
});