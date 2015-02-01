define(function(require, exports, module){
	/**
	 * theme.init() init for the theme js
	 * 
	 * @author KM@INN STUDIO
	 * 
	 */
	 
	var $ = require('modules/jquery'), jQuery = $;
	exports.config = {
		is_home : false
	
	};
	exports.init = function(){
		$(document).ready(function(){
			exports.lbox.init();
			exports.hide_no_js();
			exports.lazyload();
			exports.search();
			exports.mobile_menu.init();
			setTimeout(function(){exports.qrcode.init();},1000);
			exports.scroll_top_fixed();
			
		});
	};
	exports.scroll_top_fixed = function(){
		var $sidebar = $('#sidebar'),
			$footer = $('#footer'),
			$main_container = $('#main'),
			win_min_width = 768,
			is_desktop = true,
			$win = $(window),
			sidebar_height = $sidebar.outerHeight(),
			sidebar_width = $sidebar.outerWidth(),
			sidebar_offset_left = parseInt($sidebar.offset().left),
			sidebar_offset_top = parseInt($sidebar.offset().top),
			original_sidebar_offset_top = sidebar_offset_top,
			win_height = parseInt($win.height()),
			win_width = parseInt($win.width()),
			fixed_top = false,
			fixed_bottom = false,
			is_absolute = false,
			is_bottom = false,
			is_top = false,
			is_static = true,
			limit_bottom = parseInt($footer.offset().top - 20),
			last_scroll_pos = 0,
			footer_top,
			timer,
			resize_timer;
		if(win_width <= win_min_width) return false;
		
		function check_fit_to_float(){
			if($main_container.outerHeight() <= sidebar_height){
				return false;
			}
			if(win_width <= win_min_width){
				if($sidebar.attr('style')) $sidebar.attr('style','');
				return false;
			}
			return true;
		}
		function adjust(event){
			var type = event && event.type,
				resize = type !== 'scroll',
				win_pos = parseInt($win.scrollTop());
			limit_bottom = parseInt($footer.offset().top -20);
			//console.log(is_absolute);
			/** 
			 * scrolling down
			 */
			if(win_pos > last_scroll_pos){
				/** sidebar高度小于窗口高度*/
				if(sidebar_height <= win_height){
					/** 边栏顶高小于窗口顶高 & 边栏底高小于限制底高，进行fixedtop， */
					if((parseInt($sidebar.offset().top) <= win_pos)
					&&
					(parseInt($sidebar.offset().top + sidebar_height) < limit_bottom)){
						//console.log((parseInt($sidebar.offset().top + sidebar_height)) + ',' + limit_bottom);
						if(!fixed_top){
							$sidebar.css({
								position:'fixed',
								top:0,
								width:sidebar_width,
								bottom:''
							});
							fixed_top = true;
							fixed_bottom = false;
							is_bottom = false;
							is_top = false;
							is_absolute = false;
							is_static = false;
						}
					/** 边栏底高大于限制底高，进行绝对定位 */
					}else if((parseInt($sidebar.offset().top + sidebar_height) >= limit_bottom)){
						console.log('a');
						if(!is_absolute){
							sidebar_offset_top = $sidebar.offset().top;
							$sidebar.css({
								position:'absolute',
								top:sidebar_offset_top,
								width:sidebar_width,
								bottom:''
							});
							is_absolute = true;
							fixed_top = false;
							fixed_bottom = false;
							is_bottom = false;
							is_top = false;
							is_static = false;
						}
					}
				}else{
					/** sidebar底高大于footer顶高，则进行绝对定位 */
					if(parseInt(sidebar_height + $sidebar.offset().top) > limit_bottom){
						if(!is_absolute){
							sidebar_offset_top = $sidebar.offset().top;
							$sidebar.css({
								position:'absolute',
								top:sidebar_offset_top,
								width:sidebar_width,
								bottom:''
							});
							is_absolute = true;
							fixed_top = false;
							fixed_bottom = false;
							is_bottom = false;
							is_top = false;
							is_static = false;
						}
					/** sidebar高度，小于窗口高度则一直保持fixed top */
					//}else if(win_height >= sidebar_height){
					//	if(!fixed_top){
					//		$sidebar.css({
					//			position:'fixed',
					//			top:0,
					//			left:sidebar_offset_left,
					//			width:sidebar_width
					//		});
					//		fixed_top = true;
					//		fixed_bottom = false;
					//		is_bottom = false;
					//		is_top = false;
					//		is_absolute = false;
					//		is_static = false;
					//	}
					/** sidebar底高小于极限底高，向下滚动时进行智能浮动 */
					}else{
						/** sidebar底高大于窗口底高 & & sidebar底高小于极限顶高 & sidebar处于static状态时,不进行操作 */
						if(parseInt(sidebar_height + $sidebar.offset().top) > ($win.scrollTop() + win_height)
							&&
							parseInt(sidebar_height + $sidebar.offset().top) < limit_bottom
						){
							if(!is_static){
								sidebar_offset_top = $sidebar.offset().top;
								$sidebar.attr('style','');
								is_static = true;
								is_bottom = false;
								is_top = false;
								fixed_top = false;
								fixed_bottom = false;
								is_absolute = false;
							}
						/** sidebar底高大于窗口底高，则进行滚动 */
						}else if(parseInt(sidebar_height + $sidebar.offset().top) > ($win.scrollTop() + win_height)){
							// console.log('sidebar底高大于窗口底高，则进行滚动');
							if(!is_absolute || fixed_top){
								sidebar_offset_top = $sidebar.offset().top;
								$sidebar.css({
									position:'absolute',
									top:sidebar_offset_top,
									width:sidebar_width,
									bottom:''
								});
								is_absolute = true;
								fixed_top = false;
								fixed_bottom = false;
								is_bottom = false;
								is_top = false;
								is_static = false;
							}
						}else{
							/** 已经处于fixed bottom状态不操作 */
							if(!fixed_bottom){
								// console.log('已经处于fixed bottom状态不操作');
								$sidebar.css({
									position:'fixed',
									bottom:0,
									top:'',
									width:sidebar_width
								});
								fixed_top = false;
								fixed_bottom = true;
								is_absolute = false;
								is_bottom = false;
								is_top = false;
							}
						}
					}
				} /** end if sidebar_height <= win_height */
			/** 
			 * scrolling up
			 */
			}else if(win_pos < last_scroll_pos){
				/** 边栏高度小于窗口高度 */
				if(sidebar_height <= win_height){
					/** 边栏顶高大于窗口顶高，进行fixed top */
					if((parseInt($sidebar.offset().top) > win_pos)
						&&
						(parseInt($sidebar.offset().top) > original_sidebar_offset_top)
					){
						if(!fixed_top){
							sidebar_offset_top = $sidebar.offset().top;
							$sidebar.css({
								position:'fixed',
								top:0,
								width:sidebar_width,
								bottom:''
							});
							fixed_top = true;
							fixed_bottom = false;
							is_bottom = false;
							is_top = false;
							is_static = false;
							is_absolute = false;
						}
					/** 边栏顶高处于极限高度，不进行操作 */
					}else if(parseInt($sidebar.offset().top) <= original_sidebar_offset_top){
						if(!is_static){
							sidebar_offset_top = $sidebar.offset().top;
							$sidebar.attr('style','');
							is_static = true;
							is_bottom = false;
							is_top = false;
							fixed_top = false;
							fixed_bottom = false;
							is_absolute = false;
						}
					}
				}else{
					/** 边栏顶高小于原始边栏顶高，则取消所有css */
					if(parseInt($sidebar.offset().top) <= original_sidebar_offset_top){
						if(fixed_top){
							$sidebar.attr('style','');
							fixed_top = false;
							fixed_bottom = false;
							is_absolute = false;
							is_bottom = false;
							is_top = false;
						}
					/** 边栏顶高大于窗口顶高，则进行fixed top */
					}else if(parseInt($sidebar.offset().top) >= last_scroll_pos){
						/** 边栏顶高小于或等于初始边栏顶高，则取消所有css */
						if(parseInt($sidebar.offset().top) <= original_sidebar_offset_top){
							if(!is_static){
								$sidebar.attr('style','');
								fixed_top = false;
								fixed_bottom = false;
								is_bottom = false;
								is_top = false;
								is_static = true;
								is_absolute = false;
							}
						/** 不处于最顶处 */
						}else{
							// console.log('不再最顶');
							if(!fixed_top){
								$sidebar.css({
									position:'fixed',
									top:0,
									width:sidebar_width
								});
								fixed_top = true;
								fixed_bottom = false;
								is_bottom = false;
								is_top = false;
								is_static = false;
								is_absolute = false;
							}
						}
					/** 边栏顶高小于当前窗口顶高，则进行绝对定位 */
					}else{
						if(!is_absolute){
							sidebar_offset_top = parseInt($sidebar.offset().top);
							$sidebar.css({
								position:'absolute',
								top:sidebar_offset_top,
								bottom:''
							});
							is_absolute = true;
							fixed_top = false;
							fixed_bottom = false;
							is_bottom = false;
							is_top = false;
							is_static = false;
						}
					}
				}
			}
			// console.log('is_absolute:',is_absolute,'fixed_top:',fixed_top,'fixed_bottom:',fixed_bottom);
			/** 获取最后一次滚动的位置 */
			last_scroll_pos = win_pos;
		}
		$win.on({
			resize : resize,
			scroll : function(){
				if(!check_fit_to_float()) return;
				adjust();
				after_scroll(); /** 定时器不起作用啊，快速滚动时候，位置都乱了 */
			}
		});
		
		
		function resize(){
			clearTimeout(resize_timer);
			resize_timer = setTimeout(function(){
				if($sidebar.attr('style')) $sidebar.attr('style','');
				limit_bottom = parseInt($footer.offset().top - 20);
				sidebar_width = $sidebar.outerWidth();
				sidebar_height = $sidebar.outerHeight();
				win_width = $win.width();
			},1000);
		}
		function readjust(){
			if(parseInt($sidebar.offset().top + $sidebar.outerHeight()) >= parseInt($footer.offset().top)){
				if(!is_bottom){
					$sidebar.css({
						position:'absolute',
						top:parseInt($footer.offset().top - $sidebar.outerHeight()),
						bottom:''
					});
					is_absolute = true;
					is_bottom = true;
					fixed_top = false;
					fixed_bottom = true;
					is_top = false;
				}
			}else if(parseInt($sidebar.offset().top) < original_sidebar_offset_top){
				if(!is_top){
					$sidebar.css({
						position:'static',
						top:'',
						bottom:'',
						left:''
					});
					is_top = true;
					is_absolute = false;
					is_bottom = false;
					fixed_top = false;
					fixed_bottom = false;
				}
			}
		}
		function after_scroll(){
			clearTimeout(timer);
			timer = setTimeout(readjust,10);
		}
	}
	exports.zoom = {
		that : this,
		config : {
			content_reset_id : '.content-reset',
			img_id : '.content-reset a img'
			
		},
		init : function() {
			var _this = this,
				that = _this.that,
				$content_resets = $(_this.config.content_reset_id),
				$imgs = $(_this.config.img_id),
				scroll_ele = navigator.userAgent.toLowerCase().indexOf('webkit') === -1 ? 'html' : 'body';
			if(!$imgs[0]) return false;
			$content_resets.each(function(i){
				var $content_reset = $(this),
					$img = $content_reset.find('a>img'),
					$a = $img.parent(),
					content_reset_top = $content_reset.offset().top,
					img_small_src = $img.attr('src'),
					img_large_src = $a.attr('href');
				$a.on('click',function(){
					var $this = $(this),
						img_large = new Image();
					img_large.src = img_large_src;
					// load from cache
					if($this.hasClass('zoomed')){
						// 	scroll to content_reset_top
						if($(scroll_ele).scrollTop() > $content_reset.offset().top){
							$(scroll_ele).scrollTop($content_reset.offset().top - 80);
						}
						$img.attr({
							src : img_small_src,

						}).removeAttr('width')
						.removeAttr('height');
						$this.removeClass('zoomed');
						
					}else{
						var check = function(){
							if(img_large.width > 0 || img_large.height > 0){
								$img.attr({
									width : img_large.width,
									height : img_large.height
								});
								clearInterval(set);
							}
						};
						var set = setInterval(check,200);
						if(img_large.complete){
							$img.attr('src',img_large_src);
						}else{
							$img.fadeTo('slow','0.5',function(){
								if(img_large.complete){
									$img.fadeTo('fast',1)
										.attr('src',img_large_src);
								}
							});
						}
						$this.addClass('zoomed');
					}
					return false;
				});
			});
		}
	};
	/*
	exports.fixed_box = {
		config : {
			aside_id : '.widget-area aside'
		},
		init : function(){
			var $asides = $(exports.fixed_box.config.aside_id);
			if(!$asides[0]) return false;
			if(!exports.fixed_box.eligible_screen()) return false;
			$(window).resize(function(){
				exports.fixed_box.eligible_screen();
			});
			var $last_aside = $asides.eq($asides.length - 1),
				last_ot = $last_aside.offset().top,
				last_h = $last_aside.height(),
				last_w = $last_aside.width(),
				t;
				console.log(last_ot+last_h);
			$(window).scroll(function(){
				if(t) clearTimeout(t);
				t = setTimeout(function(){
					exports.fixed_box.fixed_action($last_aside,last_ot+last_h,last_w);
				},200);
			});
			
		},
		eligible_screen : function(){
			var w = $(window).width();
			if(w <= 768) return false;
			return true;
			// console.log(w);
		},
		fixed_action : function($fixed_ele,fixed_ele_ot,fixed_ele_w){
			if($(window).scrollTop() > fixed_ele_ot){
				$fixed_ele.
				addClass('aside-fixed')
				.css({
					'width' : fixed_ele_w + 'px'
				})
			}else{
				$fixed_ele.removeClass('aside-fixed');
			}
		}
	};
	*/
	exports.thumbnail_fix = {
		config : {
			
		},
		
		init : function(){
			var _this = this;
			_this.bind();
			$(window).resize(function(){
				_this.bind();
			});
		},
		bind : function(){
			var $a = $('.post-img-lists .post-list-link');
			if(!$a[0]) return false;
			var prev_h = 0;
			$a.each(function(i){
				var $this = $(this),
					w = $this.width(),
					h = $this.height(),
					new_h = Math.round(w*3/4),
					abs_h = Math.abs(prev_h - new_h);
					if(prev_h != 0 && abs_h > 0 && abs_h < 2){
						new_h = prev_h;
					}
				$this.height(new_h);
				prev_h = new_h;
			});
		}
	};
	exports.qrcode = {
		config : {
			id : '#qrcode',
			box_id : '#qrcode-box',
			zoom_id : '#qrcode-zoom'
		},
		cache : {},
		init : function(){
			var $qr = $(this.config.id);
			if(!$qr[0]) return false;
			var $box = $qr.find(this.config.box_id),
				$zoom = $qr.find(this.config.zoom_id);

			require.async(['modules/jquery.qrcode'],function(_a){
				$zoom.find('#qrcode-zoom-code').qrcode(window.location.href);
				$qr.fadeIn();
				$box.qrcode(window.location.href).on('click',function(){
					require.async(['modules/jquery.dialog'],function(dialog){
						$zoom.show();
						var d = dialog({
							title : false,
							quickClose: true,
							content : $zoom,
							fixed: true
						});
						d.show();
					});
				});
			
			});
			
		}
		
		
	};
	

	exports.search = function(){
		var $fm = $('.fm-search'),
			st = false;
		if(!$fm) return false;
		var $box = $fm.find('.box'),
			$input = $fm.find('[name="s"]');

		$fm.find('label').on('click',function(){
			if($fm.hasClass('active')){
				$fm.removeClass('active');
			}else{
				$fm.addClass('active');
				$input.focus().select();
			}
		});
		$input.on('blur',function(){
			st = setTimeout(function(){
				$fm.removeClass('active');
			},5000);
		});
		$input.on('focus',function(){
			st && clearTimeout(st);
		});
		$fm.on('submit',function(){
			if($.trim($input.val()) === ''){
				$input.focus();
				return false;
			}
		});
	};
	exports.hide_no_js = function(){
		var $no_js = $('.hide-no-js'),
			$on_js = $('.hide-on-js');
		$on_js[0] && $on_js.hide();
		$no_js[0] && $no_js.show();
		
	};
	exports.mobile_menu = {
		config : {
			toggle_menu_id : '.menu-mobile-toggle'
		},
		init : function(){
			var $toggle_menu = $(this.config.toggle_menu_id);
			if(!$toggle_menu[0]) return false;
			$toggle_menu.each(function(){
				$(this).find('a.toggle').on('click',function(){
					var $target_menu = $($(this).data('target'));
					$target_menu.toggle();
				});
			});
		}
	};
	/**
	 * lazyload for img
	 * 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	exports.lazyload = function(){
		var $img = $('img[data-original]');
		if(!$img[0]) return false;
		require.async(['modules/tools','modules/jquery.lazyload'],function(tools,_a){
			$img.each(function(){
				var $this = $(this);
				if(tools.in_screen($this)){
					$this.attr('src',$this.data('original'));
				}else{
					$this.lazyload();
				}
			});
		});
	};
	/**
	 * Lbox for img of post content
	 * 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	exports.lbox = {
		config : {
			img_id : '.content-reset a>img',
			no_a_img_id : '.content-reset img'
		},
		init : function(){
			var _this = this,
				$img = $(_this.config.img_id);
			if(!$img[0]) return false;
			$img.each(function(){
				$(this).parent().attr({
					'target' : '_blank',
					'rel' : 'fancybox-button'
				}).addClass('lbox');
			});
			require.async(['modules/jquery.fancybox','modules/jquery.fancybox-buttons'],function(_a,_b){
				$('.content-reset a.lbox').fancybox({
					helpers : {
						buttons	: {},
						title	: {
							type : 'float'
						}
					}
				
				});
			});
		}
	};
});