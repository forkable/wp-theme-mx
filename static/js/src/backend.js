define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools');
			
	/**
	 * admin page init js
	 */
	exports.init = function(args){
		tools.ready(function(){
			var ootab = new exports.backend_tab({
				done : args.done,
				custom : args.custom,
				tab_title: args.tab_title
			});
		})
	};
	/**
	 * Select Text
	 * 
	 * 
	 * @version 1.0.0
	 * 
	 */
	exports.select_text = {
		config : {
			input_id : '.text-select'
		},
		init : function(){
			var $inputs = document.querySelectorAll(exports.select_text.config.input_id);

			if(!$inputs[0])
				return false;

			Array.prototype.forEach.call($inputs, function($input,i){
				$input.addEventListener('click', function (e) {
					this.select();
				},false);
			});
		}
	};

	exports.backend_tab = function(args){
		this.config = {
			tab_id : 'backend-tab',
			tab_cookie_id : 'backend_default_tab',
		}

		var that = this,
			cache = {},
			$tab = I(that.config.tab_id);
		if(!$tab)
			return false;
		require('modules/jquery.kandytabs');
		var current_tab = tools.cookie.get(that.config.tab_cookie_id),
			$scroll_ele = navigator.userAgent.toLowerCase().indexOf('webkit') === -1 ? jQuery('html') : jQuery('body'),
			admin_bar_height = I('wpadminbar').offsetHeight;

		if(!current_tab) current_tab = 1;

		function get_data($cont){
			var nav_links = '',
				legends_ot = [],
				$legends = $cont.querySelectorAll('legend');
			for(var i = 0, len = $legends.length; i < len; i++){
				var $this = $legends[i],
					tx = $this.textContent;
				nav_links += '<li title="' + tx + '" data-target-index="' + i +'">' + tx + '</li>';
				legends_ot.push(parseInt($this.offsetTop));
			}
			
			return {
				nav_html : '<nav class="tabnav-container"><ul class="tabnav">'+nav_links+'</ul></nav>',
				$legends : $legends,
				legends_ot : legends_ot
			}
		}
		function scroll_to_switch($nav_links,items_ot){
			var $win = jQuery(window),
				len = items_ot.length,
				win_st = 0,
				last_active_i = 0,
				margin_t = 40;
			$nav_links[0].classList.add('active');

			$win.scroll(function(){
				win_st = $win.scrollTop();
				for(var i=0;i<=len;i++){

					if((win_st >= items_ot[i] - margin_t - admin_bar_height) && (win_st < items_ot[i + 1])){
						if(last_active_i !== i){
							for(var j = 0; j < len; j++){
								$nav_links[j].classList.remove('active');
							}
							$nav_links[i].classList.add('active');
							last_active_i = i;
						}
					}
				}
			})
		}
		function fixed_nav($nav){
			var $win = jQuery(window),
				ori_st = jQuery($nav).offset().top,
				margin_t = 0,
				placeholder_t = 20,
				is_fixed = false;

			$win.scroll(function(){
				if($win.scrollTop() >= ori_st - admin_bar_height - margin_t){
					if(!is_fixed){
						$nav.style.position = 'fixed';
						$nav.style.top = admin_bar_height + 'px';
						$nav.style.marginTop = margin_t + 'px';
						
						is_fixed = true;
					}
				}else{
					if(is_fixed){
						$nav.style.position = '';
						$nav.style.top = '';
						$nav.style.marginTop = '';
						
						is_fixed = false;
					}
				}
			});
		}
		function scroll_to_item($nav_links,$legends){
			var placeholder_t = 20,
				helper = function(i){
					$nav_links[i].addEventListener('click',function(){
						for(var j = 0; j < len; j++){
							$nav_links[j].classList.remove('active');
						}
						this.classList.add('active');
						/** scroll */
						window.scrollTo(0,i === 0 ? 0 : jQuery($legends[i]).offset().top - admin_bar_height - placeholder_t);
						/** flash */
						flash_legend($legends[i]);
					});
				};
			for(var i = 0, len = $nav_links.length; i < len; i++){
				$nav_links[i].addEventListener('click',helper(i),false);
			}
			
		}
		function flash_legend($legend){
			var $parent = $legend.parentNode,
				i = 0,
				st;
			$parent.classList.add('active');
			function toggle(last){
				if(last){
					$parent.classList.remove('active');
					return false;
				}
				if($parent.classList.contains('active')){
					$parent.classList.remove('active');
				}else{
					$parent.classList.add('active');
				}
			}
			st = setInterval(function(){
				if(i >= 10){
					toggle(true);
					clearInterval(st);
					return false;
				}
				toggle();
				i++;
			}, 70);
		}
		jQuery($tab).KandyTabs({
			delay:100,
			resize:false,
			current:current_tab,
			custom:function(b,c,i,t){
				tools.cookie.set(that.config.tab_cookie_id,i+1);
				args.custom(b,c,i,t);
				
				/**
				 * tabnav
				 */
				if(!cache.navtab)
					cache.navtab = [];
					
				if(cache.navtab[i] === true)
					return false;

				cache.navtab[i] = true;
				
				var $cont = jQuery(c[i])[0],
					data = get_data($cont),
					$nav_container = jQuery(data.nav_html)[0],
					$nav = $nav_container.querySelector('ul'),
					$nav_links = $nav_container.querySelectorAll('li'),
					legends_ot = data.legends_ot;

				$cont.insertBefore($nav_container,$cont.firstChild);
				scroll_to_switch($nav_links,legends_ot);
				scroll_to_item($nav_links,data.$legends);
				fixed_nav($nav);
			},
			done:function($btn,$cont,$tab){
				document.querySelector('.backend-tab-loading').style.display = 'none';
				$btn.eq(0).before('<span class="tab-title">' + args.tab_title +'</span>');
				$tab[0].style.display = 'block';
				args.done($btn,$cont,$tab);
				exports.select_text.init();
			}
		})
	};

	function I(e){
		return document.getElementById(e);
	}
});