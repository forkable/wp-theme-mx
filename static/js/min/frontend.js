
define(function(require,exports,module){'use strict';require.async(['modules/lazyload','modules/bootstrap-without-jq'],function(_a,_b){});var tools=require('modules/tools');exports.config={is_home:false};exports.init=function(){tools.ready(function(){exports.hide_no_js();exports.search();});}
exports.search=function(){var Q=function(s){return document.querySelector(s);},$btn=Q('.main-nav a.search');if(!$btn)
return false;var $fm=Q($btn.getAttribute('data-target')),$input=$fm.querySelector('input[type="search"]'),submit_helper=function(){if($input.value.trim()==='')
return false;};$btn.addEventListener('click',function(){setTimeout(function(){$input.focus();},100);},false);$fm.onsubmit=submit_helper;}
exports.hide_no_js=function(){var A=function(e){return document.querySelectorAll(e);},$no_js=A('.hide-no-js'),$on_js=A('.hide-on-js');if($no_js[0]){Array.prototype.forEach.call($no_js,function(el){el.style.display='none';});}
if($on_js[0]){Array.prototype.forEach.call($on_js,function(el){el.style.display='block';});}};});