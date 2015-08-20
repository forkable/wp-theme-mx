
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.config={color_prefix_id:'theme_colorful_cats-cat-color-'};exports.init=function(){tools.ready(exports.bind);};var cache={},config=exports.config;exports.bind=function(){cache.$preset_colors=I('theme_colorful_cats-preset-colors').querySelectorAll('a');cache.$cat_ids=I('theme_colorful_cats-cat-ids');cache.$cat_colors=document.querySelectorAll('.theme_colorful_cats-cat-colors');cache.$cat_ids.addEventListener('change',event_change);for(var i=0,len=cache.$cat_ids.options.length;i<len;i++){if(cache.$cat_ids.options[i].value==-1)
continue;cache.$cat_ids.options[i].style.backgroundColor='#'+get_color(cache.$cat_ids.options[i].value);cache.$cat_ids.options[i].setAttribute('data-color',get_color(cache.$cat_ids.options[i].value));}
for(var i=0,len=cache.$preset_colors.length;i<len;i++){cache.$preset_colors[i].addEventListener('click',event_preset_click);}};function event_preset_click(e){if(cache.$cat_ids.selectedIndex==0)
return false;var $opt=cache.$cat_ids.options[cache.$cat_ids.selectedIndex],color=this.getAttribute('data-color');$opt.setAttribute('data-color',color);$opt.style.backgroundColor='#'+color;set_color($opt.value,color);for(var i=0,len=cache.$preset_colors.length;i<len;i++){if(cache.$preset_colors[i].getAttribute('data-color')==color){if(!cache.$preset_colors[i].classList.contains('active'))
cache.$preset_colors[i].classList.add('active');}else{cache.$preset_colors[i].classList.remove('active');}}}
function get_color(cat_id){if(!cache.prefix_colors)
cache.prefix_colors=[];if(!cache.prefix_colors[cat_id])
cache.prefix_colors[cat_id]=I(config.color_prefix_id+cat_id).value;return cache.prefix_colors[cat_id];}
function set_color(cat_id,color){cache.prefix_colors[cat_id]=color;I(config.color_prefix_id+cat_id).value=color;}
function event_change(){var id=this.value;if(id==-1)
return;for(var i=0,len=cache.$preset_colors.length;i<len;i++){var $this_opt=this.options[this.selectedIndex],this_color=$this_opt.getAttribute('data-color');if(cache.$preset_colors[i].getAttribute('data-color')==this_color){if(!cache.$preset_colors[i].classList.contains('active'))
cache.$preset_colors[i].classList.add('active');set_color($this_opt.value,this_color);}else{if(cache.$preset_colors[i].classList.contains('active'))
cache.$preset_colors[i].classList.remove('active');}}}
function I(e){return document.getElementById(e);}});