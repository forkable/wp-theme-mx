
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.init=function(){tools.ready(function(){bind();check_redeem();});}
exports.config={process_url:'',prefix_item_id:'theme_point_lottery-item-',items_id:'.theme_point_lottery-item',add_id:'theme_point_lottery-add',control_container_id:'theme_point_lottery-control',tpl:'',lang:{M01:'Loading, please wait...',E01:'Server error, please try again later.'}}
var cache={},config=exports.config;function bind(){add();del(jQuery(config.items_id));}
function I(e,j){if(!j)
return jQuery(document.getElementById(e));return document.getElementById(e);}
function add(){var $add=I(config.add_id),$control_container=I(config.control_container_id);if(!$add[0])return false;$add.on('click',function(){var $tpl=jQuery(config.tpl.replace(/\%placeholder\%/ig,get_random_int(100,999)));del($tpl);$control_container.before($tpl);$tpl.find('input').eq(0).focus();});}
function del($tpl){$tpl.find('.delete').on('click',function(){I(jQuery(this).data('target')).css('background','#d54e21').fadeOut('slow',function(){jQuery(this).remove();})})}
function get_random_int(min,max){return new Date().getTime()+''+(Math.floor(Math.random()*(max-min+1))+min);}
function check_redeem(){cache.$tip=I('theme_point_lottery-tip',true);cache.$area_btns=I('theme_point_lottery-btns',true);cache.$user_id=I('theme_point_lottery-redeem-user-id',true);cache.$code=I('theme_point_lottery-redeem-code',true);cache.$submit=I('theme_point_lottery-check-redeem',true);function event_click(e){e.preventDefault();if(cache.$user_id.value===''){cache.$user_id.focus();return false;}
if(cache.$code.value===''){cache.$code.focus();return false;}
tip('loading',config.lang.M01);var xhr=new XMLHttpRequest(),fd={'user-id':cache.$user_id.value,redeem:cache.$code.value,type:'check-redeem'};xhr.open('get',config.process_url+'&'+tools.param(fd));xhr.send();xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText)}catch(e){data=xhr.responseText}
if(data.status){tip(data.status,data.msg);}else{tip('error',data);}}else{tip('error',config.lang.M01);}};xhr.onerror=function(){tip('error',config.lang.M01);}}
cache.$submit.addEventListener('click',event_click);function tip(t,s){cache.$tip.style.display='block';cache.$tip.innerHTML=tools.status_tip(t,s);}}});