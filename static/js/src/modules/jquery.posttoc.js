define(function(require, exports, module){
	'use strict';
	/**
	 * post_toc
	 * 
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	exports.config = {
		post_content_id : '.singular .post .post-content',
		post_toc_id : '#post-toc',
		lang : {
			M00001 : 'Post TOC',
			M00002 : '[Top]'
		}
	},
	exports.cache = {
		$post_wrap : false
		
	},
	exports.init = function(){
	
		(function ($) {
			$.fn.toc = function(options) {
				var settings = $.extend({
					id: 'container'
				}, options);

				options = $.extend(settings, options);
				
				var $hs = this.find('h1, h2, h3, h4, h5, h6');
				if(!$hs[0]) return false;
				var $toc_title = jQuery('<div class="toc-title">' + exports.config.lang.M00001 + '</div>'),
					$target = jQuery(options.id).append($toc_title),
					prevLevel = 0,
					
					getLevel = function(tagname) {
						switch(tagname){
							case 'h1': return 1;
							case 'h2': return 2;
							case 'h3': return 3;
							case 'h4': return 4;
							case 'h5': return 5;
							case 'h6': return 6;
						}
					return 0;
					},
					getUniqId = function(){
						return '__toc_id:' + Math.floor(Math.random() * 100000);
					};
				$hs.each(function() {
					var that = jQuery(this),
						currentLevel = getLevel(that[0].tagName.toLowerCase());
					if(currentLevel > prevLevel) {
						var tmp = jQuery('<ul></ul>').data('level', currentLevel);
						$target = $target.append(tmp);
						$target = tmp;
						
					}else {
						while($target.parent().length && currentLevel <= $target.parent().data('level')) {
							$target = $target.parent();
						}
					}

					var txt = that.text(),
						txtId = that.attr('id');
					if(!!!txtId) {
						txtId = getUniqId();
						that.attr({ 'id': txtId });
					}

					var alink = jQuery('<a></a>').text(txt).attr({ 'href': '#' + txtId });
					$target.append(jQuery('<li></li>').append(alink));
					prevLevel = currentLevel;
					/** 
					 * add [top] link
					 */
					var $gotop = jQuery('<a></a>').
						text(exports.config.lang.M00002)
						.attr({'href' : options.id})
						.addClass('gotop');
					$gotop.appendTo(that);
				});
				$toc_title.on('click',function(){
					jQuery(options.id).find('>ul').toggle('fast');
				
				});
				return this;
			};
		}($));
	
		var $toc = jQuery('<nav id="post-toc"></nav>'),
			$post_content = jQuery(exports.config.post_content_id);
		if(!$post_content.find('h1, h2, h3, h4, h5, h6')[0]) return false;
		$toc.insertBefore($post_content);
		$post_content.toc({id:'#post-toc'});
	}
});