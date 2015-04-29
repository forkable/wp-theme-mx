
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.config={process_url:'',lang:{M00001:'Loading, please wait'}}
var config=exports.config,cache={};exports.init=function(){tools.ready(exports.bind);}
exports.bind=function(){cache.$btn=document.getElementById('theme_page_tags-clean-cache');cache.$parent=cache.$btn.parentNode;cache.$tip=document.getElementById(cache.$btn.getAttribute('data-tip-target'));if(!cache.$btn)
return;cache.$btn.addEventListener('click',ajax);}
function ajax(el){cache.$parent.style.display='none';cache.$tip.innerHTML=tools.status_tip('loading',config.lang.M00001);var xhr=new XMLHttpRequest();xhr.open('GET',config.process_url);xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data=JSON.parse(xhr.responseText);if(data&&data.status){cache.$tip.innerHTML=tools.status_tip(data.status,data.msg);}else{cache.$tip.innerHTML=tools.status_tip('error',config.lang.E00001);}}else{cache.$tip.innerHTML=tools.status_tip('error',config.lang.E00001);}
cache.$parent.style.display='';};xhr.onerror=function(){cache.$tip.innerHTML=tools.status_tip('error',config.lang.E00001);cache.$parent.style.display='';};xhr.send();}});