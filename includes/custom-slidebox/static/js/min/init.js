
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.init=function(){tools.ready(function(){var $box=document.getElementById('slidebox');if(!$box)
return false;var $checkboxes=$box.querySelectorAll('input[type="radio"]');if($checkboxes.length<2)
return false;var i=0,t,switch_checkbox=function(){if(!t)
return false;if(i===$checkboxes.length)
i=0;$checkboxes[i].checked=true;i++;},switch_timer=function(){t=setInterval(switch_checkbox,5000);};switch_timer();$box.addEventListener('mouseover',function(){clearInterval(t);switch_timer();})});}});