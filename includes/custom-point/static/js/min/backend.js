
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.config={lang:{M00001:'Loading, please wait...',E00001:'Server error or network is disconnected.'},process_url:''}
var config=exports.config,cache={};exports.init=function(){ready(exports.bind);}
exports.bind=function(){cache.$user_id=document.getElementById('theme_custom_point-special-user-id');cache.$user_point=document.getElementById('theme_custom_point-special-point');cache.$user_event=document.getElementById('theme_custom_point-special-event');cache.$user_set=document.getElementById('theme_custom_point-special-set');cache.$user_id.addEventListener('blur',action_get_point);cache.$user_set.addEventListener('click',action_user_set);}
function action_get_point(){var $this=this,urls='&user-id='+$this.value+'&type='+$this.getAttribute('data-ajax-type');if($this.value==='')
return false;$this.style.display='none';tip($this,'loading',config.lang.M00001);var xhr=new XMLHttpRequest();xhr.open('GET',config.process_url+urls);xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText);}catch(e){}
if(data&&data.status){tip($this,data.status,data.msg);}else{tip($this,'error',config.lang.E00001);}}else{tip($this,'error',config.lang.E00001);}
$this.style.display='';xhr=null;};xhr.onerror=function(){tip($this,'error',config.lang.E00001);$this.style.display='';};xhr.send();return false;}
function action_user_set(){var $this=this,validates=[cache.$user_id,cache.$user_point,cache.$user_event],urls='';for(var i=0,len=validates.length;i<len;i++){if(validates[i].value===''){validates[i].focus();return false;}
urls+='&special['+validates[i].getAttribute('data-ajax-field')+']='+validates[i].value;}
urls+='&type=special';$this.style.display='none';tip($this,'loading',config.lang.M00001);var xhr=new XMLHttpRequest();xhr.open('GET',config.process_url+urls);xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText);}catch(e){}
if(data&&data.status){tip($this,data.status,data.msg);}else{tip($this,'error',config.lang.E00001);}}else{tip($this,'error',config.lang.E00001);}
$this.style.display='';xhr=null;};xhr.onerror=function(){tip($this,'error',config.lang.E00001);$this.style.display='';};xhr.send();return false;}
function tip(el,t,s){if(!el)
return false;var $tip=document.getElementById(el.getAttribute('data-target'));$tip.innerHTML=tools.status_tip(t,s);$tip.style.display='inline-block';}
function ready(fn){if(document.readyState!='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}});