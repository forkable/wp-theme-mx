define(function(require, exports, module){
	'use strict';
	/**
	 * admin page init js
	 */
	exports.init = function(args){
		jQuery(document).ready(function(){
			var ootab = new exports.backend_tab({
				done : args.done,
				custom : args.custom,
				tab_title: args.tab_title
			});
		});
	};
	/**
	 * Select Text
	 * 
	 * 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
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
			tab_id : '#backend-tab',
			tab_cookie_id : 'backend_default_tab',
		}

		var that = this,
			$tab = jQuery(that.config.tab_id);
		if(!$tab[0]) return false;
		require('modules/jquery.kandytabs');
		var tools = require('modules/tools'),
			current_tab = tools.cookie.get(that.config.tab_cookie_id),
			$scroll_ele = navigator.userAgent.toLowerCase().indexOf('webkit') === -1 ? jQuery('html') : jQuery('body'),
			admin_bar_height = jQuery('#wpadminbar').height();
		if(!current_tab) current_tab = 1;

		function get_data($cont){
			var nav_links = '',
				legends_ot = [],
				$legends = $cont.find('legend');
			$legends.each(function(i){
				var $this = jQuery(this),
					tx = $this.text();
				nav_links += '<li title="' + tx + '" data-target-index="' + i +'">' + tx + '</li>';
				legends_ot.push(parseInt($this.offset().top));
			});
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
			$nav_links.eq(0).addClass('active');
			$win.scroll(function(){
				win_st = $win.scrollTop();
				for(var i=0;i<=len;i++){
					//console.log(i);
					if((win_st >= items_ot[i] - margin_t - admin_bar_height) && (win_st < items_ot[i + 1])){
						if(last_active_i !== i){
							$nav_links.removeClass('active');
							$nav_links.eq(i).addClass('active');
							last_active_i = i;
						}
					}
				}
			})
		}
		function fixed_nav($nav){
			var $win = jQuery(window),
				ori_st = $nav.offset().top,
				margin_t = 0,
				placeholder_t = 20,
				is_fixed = false;

			$win.scroll(function(){
				if($win.scrollTop() >= ori_st -admin_bar_height - margin_t){
					if(!is_fixed){
						$nav.css({
							position : 'fixed',
							top : admin_bar_height,
							'margin-top' : margin_t
						});
						is_fixed = true;
					}
				}else{
					if(is_fixed){
						$nav.css({
							position : '',
							'margin-top' : '',
							top : ''
						});
						is_fixed = false;
					}
				}
			});
		}
		function scroll_to_item($nav_links,$items){
			var placeholder_t = 20;
			$nav_links.each(function(i){
				var $this = jQuery(this);
				$this.on('click',function(){
					$nav_links.removeClass('active');
					$this.addClass('active');
					$scroll_ele.animate({
						scrollTop : i === 0 ? 0 : $items.eq(i).offset().top - admin_bar_height - placeholder_t
					});
				})
			})
		}
		$tab.KandyTabs({
			delay:100,
			resize:false,
			current:current_tab,
			custom:function(b,c,i,t){
				tools.cookie.set(that.config.tab_cookie_id,i+1);
				args.custom(b,c,i,t);
				var $cont = jQuery(c[i]);
				if($cont[1]) return;
				var data = get_data($cont),
					$nav_container = jQuery(data.nav_html),
					$nav = $nav_container.find('ul'),
					$nav_links = $nav_container.find('li'),
					legends_ot = data.legends_ot;
				scroll_to_switch($nav_links,legends_ot);
				scroll_to_item($nav_links,data.$legends)
				$cont.prepend($nav_container);
				fixed_nav($nav);
				//console.log($nav_container);
			},
			done:function($btn,$cont,$tab){
				jQuery('.backend-tab-loading').hide();
				$btn.eq(0).before('<span class="tab-title">' + args.tab_title +'</span>');
				$tab.show();
				args.done($btn,$cont,$tab);
				exports.select_text.init();
			}
		})
	}

});