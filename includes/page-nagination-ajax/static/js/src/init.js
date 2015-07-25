/**
 * @version 1.0.0
 */
define(function(require, exports, module){
	'use strict';

	var tools 		= require('modules/tools');

	exports.config = {
		process_url : '',
		post_id : '',
		numpages : '',
		page : 1,
		lang : {
			M01 : 'Loading, please wait...', 
			M02 : 'Content loaded.',
			E01 : 'Sorry, some server error occurred, the operation can not be completed, please try again later.'
		}
		
	
	};
	var cache = {},
		config = exports.config;
	
	exports.init = function(){
		tools.ready(function(){
			exports.page_nagi.init();
			exports.pagi_ajax();
		});
	}
	exports.page_nagi = {
		init : function(){
			var that = this;
			cache.$post = document.querySelector('.singluar-post');
			cache.$nagi = document.querySelector('.page-pagination');
			cache.$next = cache.$nagi.querySelector('.next');
			cache.$prev = cache.$nagi.querySelector('.prev');
			cache.$next_number = cache.$next.querySelector('.current-page');
			cache.$prev_number = cache.$prev.querySelector('.current-page');
			
			if(!cache.$post || !cache.$nagi)
				return;
				
			cache.post_top;
			cache.max_bottom;
			cache.is_hide = false;

			window.addEventListener('resize',function(){
				that.reset_nagi_style()
			});

			cache.$nagi.style.display = 'block';

			this.bind();
		},
		bind : function(rebind){
			if(rebind === true){
				cache.$nagi = document.querySelector('.page-pagination');
			}
			this.reset_nagi_style();
		},
		reset_nagi_style : function(){
			cache.post_top = this.getElementTop(cache.$post);
			cache.max_bottom = cache.post_top + cache.$post.querySelector('.panel-body').clientHeight;
			cache.$nagi.style.left = this.getElementLeft(cache.$post) + 'px';
			cache.$nagi.style.width = cache.$post.clientWidth + 'px';
		},
		getElementLeft : function(e){
			var l = e.offsetLeft,
				c = e.offsetParent;
			while (c !== null){
				l += c.offsetLeft;
				c = c.offsetParent;
			}
			return l;
		},
		getElementTop : function(e){
			var l = e.offsetTop,
				c = e.offsetParent;
			while (c !== null){
				l += c.offsetTop;
				c = c.offsetParent;
			}
			return l;
		}
	};
	exports.pagi_ajax = function(){
		if(!cache.$nagi)
			return;
		cache.$post_content = document.querySelector('.post-content');
		cache.$as = cache.$nagi.querySelectorAll('a');
		console.log(cache.$as);
		for( var i = 0, len = cache.$as.length; i < len; i++){
			cache.$as[i].addEventListener('click',ajax);
		}
		function ajax(e){
			e.preventDefault();
			cache.$current = this;
			
			cache.next_pagenumber = parseInt(this.getAttribute('data-number'));
			
			tools.ajax_loading_tip('loading',config.lang.M01);
			var xhr = new XMLHttpRequest();
			xhr.open('get',config.process_url + '&page=' + config.page);
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText);}catch(e){data = xhr.responseText}
					if(data && data.status){
						done(data);
					}else{
						fail(data);
					}
				}else{
					fail();
				}
			};
			xhr.onerror = function(){
				fail();
			};
		}
		function done(data){
			if(data.status === 'success'){
				/** change page number */
				pagenumber();
				/** set html */
				cache.$post_content.innerHTML = data.content;
				/** rebind */
			}else if(data.status === 'error'){
				tools.ajax_loading_tip(data.status,data.msg);
			}
		}
		function fail(data){
			if(data){
				tools.ajax_loading_tip('error',data);
			}else{
				tools.ajax_loading_tip('error',config.lang.E01);
			}
		}
		function pagenumber(){
			/** next page */
			config.page = cache.next_pagenumber;
			if(cache.next_pagenumber > config.page){
				cache.next_pagenumber++;
				cache.$next_number.setAttribute('data-page',cache.next_pagenumber);
				cache.$prev_number.setAttribute('data-page',cache.next_pagenumber - 2);
			}else{
				cache.next_pagenumber--;
				cache.$next_number.setAttribute('data-page',cache.next_pagenumber);
				cache.$prev_number.setAttribute('data-page',cache.next_pagenumber - 2);
			}
			if(config.page == config.numpages){
				cache.$next_number.style.display = 'none';
				if(cache.$prev_number.style.display == 'none')
					cache.$prev_number.style.display = 'block';
			}else if(config.page == 1){
				cache.$prev_number.style.display = 'none';
				if(cache.$next_number.style.display == 'none')
					cache.$next_number.style.display = 'block';
			}
		}
	}

});