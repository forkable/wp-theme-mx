
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.config={lang:{M00001:'Preivew image is loading...',E00001:'Error: can not load the preview image.'}}
exports.init=function(){tools.ready(exports.hover);}
var caches={},config=exports.config;exports.hover=function(){var $lists=document.querySelectorAll('.tag-list');if(!$lists[0])
return false;Array.prototype.forEach.call($lists,function($list,i){$list.addEventListener('mouseover',load_img,false);});function load_img(){var $container=this.querySelector('.extra-thumbnail'),src=$container.getAttribute('data-img-url');if(caches[src])
return;caches[src]=1;$container.innerHTML=tools.status_tip('loading',config.lang.M00001);var $this=this,img=new Image(300,200);img.src=src;img.onload=function(){$container.innerHTML='';$container.appendChild(img);};img.onerror=function(){$container.innerHTML=tools.status_tip('error',config.lang.E00001);}}}});