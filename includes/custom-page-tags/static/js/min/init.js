
define(function(require,exports,module){'use strict';var tools=require('modules/tools'),js_request=require('theme-cache-request');exports.config={process_url:'',lang:{M00001:'Preivew image is loading...',E00001:'Error: can not load the preview image.'}}
exports.init=function(){tools.ready(exports.hover);}
var caches={},config=exports.config;exports.hover=function(){var $lists=document.querySelectorAll('.tag-list');if(!$lists[0])
return false;Array.prototype.forEach.call($lists,function($list,i){$list.addEventListener('mouseover',load_img,false);});function load_img(){var $container=this.querySelector('.extra-thumbnail'),$a=this.querySelector('a'),title=$a.getAttribute('title'),url=$a.getAttribute('data-thumbnail-url');if(caches[title])
return;caches[title]=1;$container.innerHTML=tools.status_tip('loading',config.lang.M00001);var img=new Image(300,200);img.src=url;img.onload=function(){$container.innerHTML='';$container.appendChild(img);};img.onerror=function(){fail();};function fail(msg){if(!msg)
msg=config.lang.E00001;$container.innerHTML=tools.status_tip('error',msg);}}}});